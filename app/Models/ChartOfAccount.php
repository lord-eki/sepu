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

    /** Recursive children for full subtree loading */
    public function allChildren()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_account_id')
                    ->with('allChildren');
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    /** Budget items that reference this COA account */
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

    public function scopePostable($query)
    {
        // Postable = leaf accounts (not headers, not group summaries)
        return $query->whereNotIn('account_type', ['header'])
                     ->whereDoesntHave('childAccounts');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_account_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /**
     * Returns true if this account is a leaf (no children) and can be
     * directly posted to in journal entries.
     */
    public function isPostable(): bool
    {
        return !in_array($this->account_type, ['header'])
            && $this->childAccounts()->doesntExist();
    }

    /**
     * Determine the account's normal balance side.
     * Assets & Expenses → debit; Liabilities, Equity, Revenue → credit.
     */
    public function getNormalBalanceSide(): string
    {
        if ($this->normal_balance) {
            return $this->normal_balance;
        }

        return in_array($this->account_type, ['asset', 'expense'])
            ? 'debit'
            : 'credit';
    }

    /**
     * Compute the running balance from posted journal entry lines for a period.
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
     * Human-readable label for account_type.
     */
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

    /**
     * Badge colour hint for the UI .
     */
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