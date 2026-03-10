<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChartOfAccountController extends Controller
{
    // ── Enums / constants used in forms ─────────────────────────────────

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
        'current_asset'          => 'Current Asset',
        'fixed_asset'            => 'Fixed Asset',
        'current_liability'      => 'Current Liability',
        'long_term_liability'    => 'Long-term Liability',
        'member_equity'          => 'Member Equity',
        'retained_earnings'      => 'Retained Earnings',
        'operating_revenue'      => 'Operating Revenue',
        'non_operating_revenue'  => 'Non-operating Revenue',
        'operating_expense'      => 'Operating Expense',
        'non_operating_expense'  => 'Non-operating Expense',
    ];

    // ── INDEX ────────────────────────────────────────────────────────────

    /**
     * Display the full hierarchical Chart of Accounts.
     * Returns the tree (roots + recursive children) plus flat list for search.
     */
    public function index(Request $request)
    {
        // Flat list with optional search / filter
        $query = ChartOfAccount::with('parentAccount')
            ->orderBy('account_code');

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

        // Tree (only when no search – full hierarchical view)
        $tree = null;
        if (!$request->filled('search') && !$request->filled('type')) {
            $roots = ChartOfAccount::with('allChildren')
                ->whereNull('parent_account_id')
                ->orderBy('account_code')
                ->get();
            $tree = $roots->map(fn($a) => $this->formatTree($a));
        }

        // Summary stats
        $stats = [
            'total'    => ChartOfAccount::count(),
            'active'   => ChartOfAccount::where('is_active', true)->count(),
            'postable' => ChartOfAccount::whereNotIn('account_type', ['header'])
                            ->whereDoesntHave('childAccounts')->count(),
            'by_type'  => ChartOfAccount::selectRaw('account_type, count(*) as count')
                            ->groupBy('account_type')->pluck('count', 'account_type'),
        ];

        return Inertia::render('Finance/ChartOfAccounts/Index', [
            'accounts'         => $flatAccounts,
            'tree'             => $tree,
            'stats'            => $stats,
            'filters'          => $request->only(['search', 'type', 'status']),
            'accountTypes'     => self::ACCOUNT_TYPES,
            'accountCategories'=> self::ACCOUNT_CATEGORIES,
        ]);
    }

    // ── CREATE ───────────────────────────────────────────────────────────

    public function create()
    {
        return Inertia::render('Finance/ChartOfAccounts/Create', [
            'parentAccounts'   => $this->getParentOptions(),
            'accountTypes'     => self::ACCOUNT_TYPES,
            'accountCategories'=> self::ACCOUNT_CATEGORIES,
        ]);
    }

    // ── STORE ────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_code'     => 'required|string|max:20|unique:charts_of_accounts,account_code',
            'account_name'     => 'required|string|max:255',
            'account_type'     => 'required|in:' . implode(',', array_keys(self::ACCOUNT_TYPES)),
            'account_category' => 'required|in:' . implode(',', array_keys(self::ACCOUNT_CATEGORIES)),
            'normal_balance'   => 'required|in:debit,credit',
            'parent_account_id'=> 'nullable|exists:charts_of_accounts,id',
            'description'      => 'nullable|string',
            'opening_balance'  => 'nullable|numeric|min:0',
            'is_active'        => 'boolean',
        ]);

        // Compute level from parent
        $level = 1;
        if (!empty($data['parent_account_id'])) {
            $parent = ChartOfAccount::find($data['parent_account_id']);
            $level  = ($parent?->level ?? 0) + 1;
        }

        $account = ChartOfAccount::create(array_merge($data, [
            'opening_balance' => $data['opening_balance'] ?? 0,
            'current_balance' => $data['opening_balance'] ?? 0,
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

    // ── EDIT ─────────────────────────────────────────────────────────────

    public function edit(ChartOfAccount $chartOfAccount)
    {
        return Inertia::render('Finance/ChartOfAccounts/Edit', [
            'account'          => $this->formatAccount($chartOfAccount),
            'parentAccounts'   => $this->getParentOptions($chartOfAccount->id),
            'accountTypes'     => self::ACCOUNT_TYPES,
            'accountCategories'=> self::ACCOUNT_CATEGORIES,
        ]);
    }

    // ── UPDATE ───────────────────────────────────────────────────────────

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        // System accounts: only name, description, active flag can change
        $rules = [
            'account_name'     => 'required|string|max:255',
            'description'      => 'nullable|string',
            'is_active'        => 'boolean',
        ];

        if (!$chartOfAccount->is_system_account) {
            $rules = array_merge($rules, [
                'account_code'     => "required|string|max:20|unique:charts_of_accounts,account_code,{$chartOfAccount->id}",
                'account_type'     => 'required|in:' . implode(',', array_keys(self::ACCOUNT_TYPES)),
                'account_category' => 'required|in:' . implode(',', array_keys(self::ACCOUNT_CATEGORIES)),
                'normal_balance'   => 'required|in:debit,credit',
                'parent_account_id'=> 'nullable|exists:charts_of_accounts,id',
                'opening_balance'  => 'nullable|numeric|min:0',
            ]);
        }

        $data = $request->validate($rules);

        // Prevent circular parent assignment
        if (!empty($data['parent_account_id'])) {
            if ($data['parent_account_id'] == $chartOfAccount->id) {
                return back()->withErrors(['parent_account_id' => 'An account cannot be its own parent.']);
            }

            // Check if chosen parent is a descendant
            if ($this->isDescendant($chartOfAccount->id, $data['parent_account_id'])) {
                return back()->withErrors(['parent_account_id' => 'Cannot set a child account as the parent (circular reference).']);
            }

            // Recalculate level
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

    // ── API helpers for dropdowns ────────────────────────────────────────

    /**
     * JSON endpoint – returns postable (leaf) accounts for journal entry dropdowns.
     */
    public function postableAccounts(Request $request)
    {
        $accounts = ChartOfAccount::active()
            ->postable()
            ->when($request->type, fn($q, $t) => $q->where('account_type', $t))
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name', 'account_type', 'normal_balance']);

        return response()->json($accounts);
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function formatAccount(ChartOfAccount $account): array
    {
        return [
            'id'                => $account->id,
            'account_code'      => $account->account_code,
            'account_name'      => $account->account_name,
            'account_type'      => $account->account_type,
            'account_type_label'=> $account->getTypeLabel(),
            'type_badge_color'  => $account->getTypeBadgeColor(),
            'account_category'  => $account->account_category,
            'normal_balance'    => $account->normal_balance,
            'parent_account_id' => $account->parent_account_id,
            'parent_name'       => $account->parentAccount?->account_name,
            'description'       => $account->description,
            'opening_balance'   => (float) $account->opening_balance,
            'current_balance'   => (float) $account->current_balance,
            'is_active'         => $account->is_active,
            'is_system_account' => $account->is_system_account,
            'level'             => $account->level,
            'is_postable'       => $account->isPostable(),
            'created_at'        => $account->created_at?->format('Y-m-d'),
        ];
    }

    private function formatTree(ChartOfAccount $account): array
    {
        $node = $this->formatAccount($account);
        $node['children'] = $account->allChildren
            ->sortBy('account_code')
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
                'id'    => $a->id,
                'label' => str_repeat('  ', $a->level - 1) . $a->account_code . ' – ' . $a->account_name,
                'type'  => $a->account_type,
            ])
            ->toArray();
    }

    /**
     * Check if $potentialDescendantId is a descendant of $accountId.
     */
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