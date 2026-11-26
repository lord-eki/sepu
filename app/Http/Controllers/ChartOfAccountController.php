<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChartOfAccountRequest;
use App\Models\AccountCategory;
use App\Models\ChartOfAccount;
use App\Support\AccountTypes;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ChartOfAccountController extends Controller
{


    public const ACCOUNT_CATEGORIES = [];

    // all categories .

    private function getCategories(): array
    {
        return AccountCategory::orderBy('label')->get(['id', 'key', 'label', 'is_system'])->toArray();
    }

    //Index
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
            'accountTypes'      => AccountTypes::ACCOUNT_TYPES,
            'accountCategories' => $this->getCategories(),
        ]);
    }

    // create 

    public function create()
    {
        return Inertia::render('Finance/ChartOfAccounts/Create', [
            'parentAccounts'    => $this->getParentOptions(),
            'accountTypes'      => AccountTypes::ACCOUNT_TYPES,
            'accountCategories' => $this->getCategories(),
        ]);
    }

    //Persist a chart of account
    public function store(StoreChartOfAccountRequest $request)
    {
        $data = $request->validated();

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

    //Show chart of account
    public function show(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->load(['parentAccount', 'childAccounts.childAccounts']);

        return Inertia::render('Finance/ChartOfAccounts/Show', [
            'account'      => $this->formatAccount($chartOfAccount),
            'children'     => $chartOfAccount->childAccounts->map(fn($c) => $this->formatAccount($c)),
            'accountTypes' => AccountTypes::ACCOUNT_TYPES,
        ]);
    }

    //edit chart of account
    public function edit(ChartOfAccount $chartOfAccount)
    {
        return Inertia::render('Finance/ChartOfAccounts/Edit', [
            'account'           => $this->formatAccount($chartOfAccount),
            'parentAccounts'    => $this->getParentOptions($chartOfAccount->id),
            'accountTypes'      => AccountTypes::ACCOUNT_TYPES,
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
                'account_type'      => 'required|in:' . implode(',', array_keys(AccountTypes::ACCOUNT_TYPES)),
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

    //Delete
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



    public function toggleActive(ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->is_system_account && $chartOfAccount->is_active) {
            return back()->withErrors(['error' => 'System accounts cannot be deactivated.']);
        }

        $chartOfAccount->update(['is_active' => !$chartOfAccount->is_active]);
        $status = $chartOfAccount->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Account {$chartOfAccount->account_code} {$status}.");
    }


    public function nextCode(Request $request)
    {
        $parentId = $request->query('parent_id');

        if (!$parentId) {
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

        $step = match (true) {
            $parent->level === 1 => 1000,
            $parent->level === 2 => 100,
            default              => 1,
        };

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

        $key = Str::slug($data['label'], '_');

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

    //helpers

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
