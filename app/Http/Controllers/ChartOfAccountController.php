<?php

namespace App\Http\Controllers;

use App\Models\AccountCategory;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChartOfAccountController extends Controller
{
    public const ACCOUNT_TYPES = [
        'asset'        => 'Asset',
        'liability'    => 'Liability',
        'equity'       => 'Equity',
        'revenue'      => 'Income / Revenue',
        'expense'      => 'Expense',
        'contra_asset' => 'Contra Asset',
        'header'       => 'Header / Group',
    ];

    public const ACCOUNT_CATEGORIES = [];

    /**
     * Returns all categories from the DB as an array of objects.
     */
    private function getCategories(): array
    {
        return AccountCategory::orderBy('label')->get(['id', 'key', 'label', 'is_system'])->toArray();
    }

    // ── INDEX ────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = ChartOfAccount::with('parentAccount')->orderBy('account_code');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('account_code',  'like', "%{$s}%")
                  ->orWhere('account_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('account_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $flatAccounts = $query->get()->map(fn($a) => $this->formatAccount($a));

        $tree = null;
        if (!$request->filled('search') && !$request->filled('type')) {
            $roots = ChartOfAccount::with('allChildren')
                ->whereNull('parent_account_id')
                ->orderBy('account_code')
                ->get();
            $tree = $roots->map(fn($a) => $this->formatTree($a));
        }

        $stats = [
            'total'    => ChartOfAccount::count(),
            'active'   => ChartOfAccount::where('is_active', true)->count(),
            'postable' => ChartOfAccount::whereNotIn('account_type', ['header'])
                            ->whereDoesntHave('childAccounts')->count(),
            'by_type'  => ChartOfAccount::selectRaw('account_type, count(*) as count')
                            ->groupBy('account_type')->pluck('count', 'account_type'),
        ];

        return Inertia::render('Finance/ChartOfAccounts/Index', [
            'accounts'          => $flatAccounts,
            'tree'              => $tree,
            'stats'             => $stats,
            'filters'           => $request->only(['search', 'type', 'status']),
            'accountTypes'      => self::ACCOUNT_TYPES,
            'accountCategories' => $this->getCategories(),
        ]);
    }

    // ── CREATE ───────────────────────────────────────────────────────────

    public function create()
    {
        return Inertia::render('Finance/ChartOfAccounts/Create', [
            'parentAccounts'    => $this->getParentOptions(),
            'accountTypes'      => self::ACCOUNT_TYPES,
            'accountCategories' => $this->getCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_code'      => 'required|string|max:20|unique:charts_of_accounts,account_code',
            'account_name'      => 'required|string|max:255',
            'account_type'      => 'required|in:' . implode(',', array_keys(self::ACCOUNT_TYPES)),
            'account_category'  => 'required|string|max:60|exists:account_categories,key',
            'normal_balance'    => 'required|in:debit,credit',
            'parent_account_id' => 'nullable|exists:charts_of_accounts,id',
            'description'       => 'nullable|string',
            'opening_balance'   => 'nullable|numeric|min:0',
            'is_active'         => 'boolean',
        ]);

        $level = 1;
        if (!empty($data['parent_account_id'])) {
            $parent = ChartOfAccount::find($data['parent_account_id']);
            $level  = ($parent?->level ?? 0) + 1;
        }

        $account = ChartOfAccount::create(array_merge($data, [
            'opening_balance'   => $data['opening_balance'] ?? 0,
            'current_balance'   => $data['opening_balance'] ?? 0,
            'is_system_account' => false,
            'level'             => $level,
        ]));

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', "Account {$account->account_code} – {$account->account_name} created successfully.");
    }

    // ── SHOW ─────────────────────────────────────────────────────────────

    public function show(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->load(['parentAccount', 'childAccounts.childAccounts']);

        return Inertia::render('Finance/ChartOfAccounts/Show', [
            'account'      => $this->formatAccount($chartOfAccount),
            'children'     => $chartOfAccount->childAccounts->map(fn($c) => $this->formatAccount($c)),
            'accountTypes' => self::ACCOUNT_TYPES,
        ]);
    }

    // ── EDIT / UPDATE ────────────────────────────────────────────────────

    public function edit(ChartOfAccount $chartOfAccount)
    {
        return Inertia::render('Finance/ChartOfAccounts/Edit', [
            'account'           => $this->formatAccount($chartOfAccount),
            'parentAccounts'    => $this->getParentOptions($chartOfAccount->id),
            'accountTypes'      => self::ACCOUNT_TYPES,
            'accountCategories' => $this->getCategories(),
        ]);
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $rules = [
            'account_name' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'is_active'    => 'boolean',
        ];

        if (!$chartOfAccount->is_system_account) {
            $rules = array_merge($rules, [
                'account_code'      => "required|string|max:20|unique:charts_of_accounts,account_code,{$chartOfAccount->id}",
                'account_type'      => 'required|in:' . implode(',', array_keys(self::ACCOUNT_TYPES)),
                'account_category'  => 'required|string|max:60|exists:account_categories,key',
                'normal_balance'    => 'required|in:debit,credit',
                'parent_account_id' => 'nullable|exists:charts_of_accounts,id',
                'opening_balance'   => 'nullable|numeric|min:0',
            ]);
        }

        $data = $request->validate($rules);

        if (!empty($data['parent_account_id'])) {
            if ($data['parent_account_id'] == $chartOfAccount->id) {
                return back()->withErrors(['parent_account_id' => 'An account cannot be its own parent.']);
            }
            if ($this->isDescendant($chartOfAccount->id, $data['parent_account_id'])) {
                return back()->withErrors(['parent_account_id' => 'Cannot set a child account as the parent (circular reference).']);
            }
            $parent        = ChartOfAccount::find($data['parent_account_id']);
            $data['level'] = ($parent?->level ?? 0) + 1;
        }

        $chartOfAccount->update($data);

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', "Account {$chartOfAccount->account_code} updated successfully.");
    }

    // ── DESTROY ──────────────────────────────────────────────────────────

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->is_system_account) {
            return back()->withErrors(['error' => 'System accounts cannot be deleted.']);
        }
        if ($chartOfAccount->childAccounts()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete an account that has sub-accounts. Remove or re-assign the sub-accounts first.']);
        }

        $chartOfAccount->delete();

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', "Account {$chartOfAccount->account_code} archived successfully.");
    }

    // ── TOGGLE ACTIVE ────────────────────────────────────────────────────

    public function toggleActive(ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->is_system_account && $chartOfAccount->is_active) {
            return back()->withErrors(['error' => 'System accounts cannot be deactivated.']);
        }

        $chartOfAccount->update(['is_active' => !$chartOfAccount->is_active]);
        $status = $chartOfAccount->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Account {$chartOfAccount->account_code} {$status}.");
    }

    // ── JSON API ENDPOINTS ───────────────────────────────────────────────

    /**
     * GET /api/chart-of-accounts/next-code?parent_id=5
     *
     * Returns the next available 5-digit account code for a given parent (or root).
     *
     * Observed coding scheme (all codes are 5 digits):
     *   Level 1 – root   : step 10000  →  10000, 20000, 30000, 40000, 50000
     *   Level 2          : step  1000  →  11000, 12000, 13000  (under 10000)
     *   Level 3          : step   100  →  11100, 11200, 11300  (under 11000)
     *   Level 4+         : step     1  →  11201, 11202, 11203  (under 11200)
     *
     *      */
    public function nextCode(Request $request)
    {
        $parentId = $request->query('parent_id');

        if (!$parentId) {
            // Root level — multiples of 10000, minimum 10000
            $codes = ChartOfAccount::whereNull('parent_account_id')
                ->pluck('account_code')
                ->map(fn($c) => (int) preg_replace('/\D/', '', $c))
                ->filter()
                ->sort()
                ->values();

            $next = $codes->isEmpty()
                ? 10000
                : (int) (floor($codes->last() / 10000) + 1) * 10000;

            return response()->json(['next_code' => str_pad((string) $next, 5, '0', STR_PAD_LEFT)]);
        }

        $parent = ChartOfAccount::find($parentId);

        if (!$parent) {
            return response()->json(['error' => 'Parent not found'], 404);
        }

        $parentCode = (int) preg_replace('/\D/', '', $parent->account_code);

        // Step size :
        //   level 1 → children step by 1000  (e.g. 10000 → 11000, 12000…)
        //   level 2 → children step by 100   (e.g. 11000 → 11100, 11200…)
        //   level 3+ → children step by 1    (e.g. 11200 → 11201, 11202…)
        $step = match (true) {
            $parent->level === 1 => 1000,
            $parent->level === 2 => 100,
            default              => 1,
        };

        // Find the highest existing child code
        $maxChild = ChartOfAccount::where('parent_account_id', $parentId)
            ->pluck('account_code')
            ->map(fn($c) => (int) preg_replace('/\D/', '', $c))
            ->max();

        $next = $maxChild
            ? $maxChild + $step
            : $parentCode + $step;

        return response()->json(['next_code' => str_pad((string) $next, 5, '0', STR_PAD_LEFT)]);
    }

    /**
     * GET /api/chart-of-accounts/postable?type=expense
     */
    public function postableAccounts(Request $request)
    {
        $accounts = ChartOfAccount::active()
            ->postable()
            ->when($request->type, fn($q, $t) => $q->where('account_type', $t))
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name', 'account_type', 'normal_balance', 'level']);

        return response()->json(
            $accounts->map(fn($a) => [
                'id'             => $a->id,
                'account_code'   => $a->account_code,
                'account_name'   => $a->account_name,
                'account_type'   => $a->account_type,
                'normal_balance' => $a->normal_balance,
                'label'          => $a->dropdown_label,
            ])
        );
    }

    /**
     * GET /api/chart-of-accounts/budget-lines
     */
    public function budgetLineAccounts(Request $request)
    {
        $query = ChartOfAccount::active()
            ->postable()
            ->with('parentAccount.parentAccount.parentAccount.parentAccount')
            ->orderBy('account_code');

        if ($request->filled('type')) {
            $query->where('account_type', $request->type);
        } elseif ($request->filled('types')) {
            $query->whereIn('account_type', explode(',', $request->types));
        }

        return response()->json(
            $query->get()->map(fn($a) => [
                'id'             => $a->id,
                'account_code'   => $a->account_code,
                'account_name'   => $a->account_name,
                'account_type'   => $a->account_type,
                'normal_balance' => $a->normal_balance,
                'level'          => $a->level,
                'label'          => $a->dropdown_label,
                'full_path'      => $a->full_path_name,
            ])
        );
    }

    /**
     * GET /api/chart-of-accounts/tree
     */
    public function accountTree()
    {
        $roots = ChartOfAccount::active()
            ->with('allChildren')
            ->whereNull('parent_account_id')
            ->orderBy('account_code')
            ->get();

        return response()->json($roots->map(fn($a) => $this->formatTree($a)));
    }

    /**
     *
     * Creates a new user-defined category 
     */
    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:120',
        ]);

        $key = \Str::slug($data['label'], '_');

        // If the key already exists just return the existing record
        $category = AccountCategory::firstOrCreate(
            ['key' => $key],
            ['label' => $data['label'], 'is_system' => false]
        );

        return response()->json([
            'id'        => $category->id,
            'key'       => $category->key,
            'label'     => $category->label,
            'is_system' => $category->is_system,
        ], 201);
    }

    // ── PRIVATE HELPERS ──────────────────────────────────────────────────

    private function formatAccount(ChartOfAccount $account): array
    {
        return [
            'id'                 => $account->id,
            'account_code'       => $account->account_code,
            'account_name'       => $account->account_name,
            'account_type'       => $account->account_type,
            'account_type_label' => $account->getTypeLabel(),
            'type_badge_color'   => $account->getTypeBadgeColor(),
            'account_category'   => $account->account_category,
            'normal_balance'     => $account->normal_balance,
            'parent_account_id'  => $account->parent_account_id,
            'parent_name'        => $account->parentAccount?->account_name,
            'description'        => $account->description,
            'opening_balance'    => (float)$account->opening_balance,
            'current_balance'    => (float)$account->current_balance,
            'is_active'          => $account->is_active,
            'is_system_account'  => $account->is_system_account,
            'level'              => $account->level,
            'is_postable'        => $account->isPostable(),
            'full_path'          => $account->full_path_name,
            'dropdown_label'     => $account->dropdown_label,
            'created_at'         => $account->created_at?->format('Y-m-d'),
        ];
    }

    private function formatTree(ChartOfAccount $account): array
    {
        $node             = $this->formatAccount($account);
        $node['children'] = $account->allChildren
            ->map(fn($child) => $this->formatTree($child))
            ->values()
            ->toArray();
        return $node;
    }

    private function getParentOptions(?int $excludeId = null): array
    {
        return ChartOfAccount::active()
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name', 'level', 'account_type'])
            ->map(fn($a) => [
                'id'           => $a->id,
                'label'        => str_repeat('　', max(0, ($a->level ?? 1) - 1)) . $a->account_code . ' – ' . $a->account_name,
                'type'         => $a->account_type,
                'level'        => $a->level,
                'account_code' => $a->account_code,
            ])
            ->toArray();
    }

    private function isDescendant(int $accountId, int $potentialDescendantId): bool
    {
        $children = ChartOfAccount::where('parent_account_id', $accountId)->pluck('id');
        foreach ($children as $childId) {
            if ($childId === $potentialDescendantId) return true;
            if ($this->isDescendant($childId, $potentialDescendantId)) return true;
        }
        return false;
    }
}