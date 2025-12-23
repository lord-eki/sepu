<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    //
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'account_category',
        'parent_account_id',
        'description',
        'opening_balance',
        'current_balance',
        'is_active',
        'is_system_account',
        'level',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_system_account' => 'boolean',
        'level' => 'integer',
    ];

    // Relationships
    public function parentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_account_id');
    }

    public function childAccounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_account_id');
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    // Get account balance for a period
    public function getBalance($startDate = null, $endDate = null)
    {
        $query = $this->journalEntryLines()
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted');
                if ($startDate) $q->where('entry_date', '>=', $startDate);
                if ($endDate) $q->where('entry_date', '<=', $endDate);
            });

        $debits = $query->where('entry_type', 'debit')->sum('amount');
        $credits = $query->where('entry_type', 'credit')->sum('amount');

        // Asset & Expense accounts: Debit increases, Credit decreases
        if (in_array($this->account_type, ['asset', 'expense'])) {
            return $debits - $credits;
        }
        // Liability, Equity & Revenue: Credit increases, Debit decreases
        return $credits - $debits;
    }
}
