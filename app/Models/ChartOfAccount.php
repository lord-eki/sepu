<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'charts_of_accounts';

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'account_category',
        'normal_balance',
        'parent_account_id',
        'description',
        'opening_balance',
        'current_balance',
        'is_active',
        'is_system_account',
        'level',
    ];

    protected $casts = [
        'opening_balance'   => 'decimal:2',
        'current_balance'   => 'decimal:2',
        'is_active'         => 'boolean',
        'is_system_account' => 'boolean',
        'level'             => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function parentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_account_id');
    }

    public function childAccounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_account_id');
    }

    /**
     * Recursively loads the full subtree in a single eager load.
     * Usage: ChartOfAccount::with('allChildren')->whereNull('parent_account_id')->get()
     */
    public function allChildren()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_account_id')
                    ->with('allChildren')
                    ->orderBy('account_code');
    }

    /**
     * Recursive parent chain — lets you build the full path label
     * without extra queries when eager-loaded.
     * Usage: $account->load('parentChain')
     */
    public function parentChain()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_account_id')
                    ->with('parentChain');
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    /** Budget lines that use this COA account */
    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class, 'chart_of_account_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Postable = leaf accounts (no children, not a header).
     * These are the only accounts that can be directly debited/credited.
     */
    public function scopePostable($query)
    {
        return $query->whereNotIn('account_type', ['header'])
                     ->whereDoesntHave('childAccounts');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_account_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function isPostable(): bool
    {
        return !in_array($this->account_type, ['header'])
            && $this->childAccounts()->doesntExist();
    }

    public function getNormalBalanceSide(): string
    {
        if ($this->normal_balance) {
            return $this->normal_balance;
        }
        return in_array($this->account_type, ['asset', 'expense']) ? 'debit' : 'credit';
    }

    /**
     * Running balance from posted journal lines for an optional date range.
     */
    public function getBalance(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->journalEntryLines()
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted');
                if ($startDate) $q->where('entry_date', '>=', $startDate);
                if ($endDate)   $q->where('entry_date', '<=', $endDate);
            });

        $debits  = (clone $query)->where('entry_type', 'debit')->sum('amount');
        $credits = (clone $query)->where('entry_type', 'credit')->sum('amount');

        return $this->getNormalBalanceSide() === 'debit'
            ? (float)($debits - $credits)
            : (float)($credits - $debits);
    }

    /**
     * Recursively aggregates balance for this account and all its descendants.
     * Powers the hierarchical totals in Balance Sheet and Income Statement.
     */
    public function getTotalBalance(?string $startDate = null, ?string $endDate = null): float
    {
        $total = $this->getBalance($startDate, $endDate);
        foreach ($this->childAccounts as $child) {
            $total += $child->getTotalBalance($startDate, $endDate);
        }
        return $total;
    }

    /**
     * Full ancestry path string.
     * e.g. "Assets > Current Assets > Bank Accounts > Bank Operations Account"
     *
     * Walks the already-loaded parentAccount chain — no extra queries when
     * the relation is eager-loaded.
     */
    public function getFullPathNameAttribute(): string
    {
        $parts  = [$this->account_name];
        $parent = $this->parentAccount;
        while ($parent) {
            array_unshift($parts, $parent->account_name);
            $parent = $parent->parentAccount;
        }
        return implode(' > ', $parts);
    }

    /**
     * Indented label for flat <select> / combo-box dropdowns.
     * Indentation is derived from the stored `level` column — no parent
     * traversal needed at render time.
     *
     * Example (level 4):  "　　　51101 – Salaries & Wages"
     */
    public function getDropdownLabelAttribute(): string
    {
        $indent = str_repeat('　', max(0, ($this->level ?? 1) - 1));
        return $indent . $this->account_code . ' – ' . $this->account_name;
    }

    public function getTypeLabel(): string
    {
        return match($this->account_type) {
            'asset'        => 'Asset',
            'liability'    => 'Liability',
            'equity'       => 'Equity',
            'revenue'      => 'Income',
            'expense'      => 'Expense',
            'contra_asset' => 'Contra Asset',
            'header'       => 'Header / Group',
            default        => ucfirst($this->account_type),
        };
    }

    public function getTypeBadgeColor(): string
    {
        return match($this->account_type) {
            'asset'        => 'blue',
            'liability'    => 'red',
            'equity'       => 'purple',
            'revenue'      => 'green',
            'expense'      => 'orange',
            'contra_asset' => 'yellow',
            'header'       => 'gray',
            default        => 'gray',
        };
    }
}