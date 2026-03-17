<?php

namespace App\Http\Controllers;

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

    public const ACCOUNT_CATEGORIES = [
        'current_asset'         => 'Current Asset',
        'fixed_asset'           => 'Fixed Asset',
        'current_liability'     => 'Current Liability',
        'long_term_liability'   => 'Long-term Liability',
        'member_equity'         => 'Member Equity',
        'retained_earnings'     => 'Retained Earnings',
        'operating_revenue'     => 'Operating Revenue',
        'non_operating_revenue' => 'Non-operating Revenue',
        'operating_expense'     => 'Operating Expense',
        'non_operating_expense' => 'Non-operating Expense',
    ];

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
            'accountCategories' => self::ACCOUNT_CATEGORIES,
        ]);
    }

    // ── CREATE  ───────────────────────────────────────────────────

    public function create()
    {
        return Inertia::render('Finance/ChartOfAccounts/Create', [
            'parentAccounts'    => $this->getParentOptions(),
            'accountTypes'      => self::ACCOUNT_TYPES,
            'accountCategories' => self::ACCOUNT_CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_code'      => 'required|string|max:20|unique:charts_of_accounts,account_code',
            'account_name'      => 'required|string|max:255',
            'account_type'      => 'required|in:' . implode(',', array_keys(self::ACCOUNT_TYPES)),
            'account_category'  => 'required|in:' . implode(',', array_keys(self::ACCOUNT_CATEGORIES)),
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
            'accountCategories' => self::ACCOUNT_CATEGORIES,
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
                'account_category'  => 'required|in:' . implode(',', array_keys(self::ACCOUNT_CATEGORIES)),
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
     * GET /api/chart-of-accounts/postable?type=expense
     *
     * Postable leaf accounts for journal entry line dropdowns.
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
     * GET /api/chart-of-accounts/budget-lines?type=expense
     * GET /api/chart-of-accounts/budget-lines?types=expense,revenue
     *
     * All active postable (leaf) accounts for the budget-item form dropdown.
     * Returns indented labels and full path hints so users see hierarchy context.
     *
     * This is the endpoint your budget Create/Edit forms should call to
     * populate the "Account / Budget Line" dropdown 
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
                'label'          => $a->dropdown_label,  // indented for flat <select>
                'full_path'      => $a->full_path_name,  // breadcrumb hint in UI
            ])
        );
    }

    /**
     * GET /api/chart-of-accounts/tree
     *
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

    /**
     * Flat indented list for parent-account <select> dropdowns.
     */
    private function getParentOptions(?int $excludeId = null): array
    {
        return ChartOfAccount::active()
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name', 'level', 'account_type'])
            ->map(fn($a) => [
                'id'    => $a->id,
                'label' => str_repeat('　', max(0, ($a->level ?? 1) - 1)) . $a->account_code . ' – ' . $a->account_name,
                'type'  => $a->account_type,
                'level' => $a->level,
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