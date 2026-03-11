<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
     use HasFactory, SoftDeletes;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'total_debit',
        'total_credit',
        'status',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    // Relationships
    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // Check if balanced
    public function isBalanced()
    {
        return round($this->total_debit, 2) == round($this->total_credit, 2);
    }

    // Post the journal entry
    public function post()
    {
        if (!$this->isBalanced()) {
            throw new \Exception('Journal entry is not balanced');
        }

        $this->update([
            'status' => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        // Update account balances
        foreach ($this->lines as $line) {
            $account = $line->account;
            $amount = $line->amount;
            
            if ($line->entry_type === 'debit') {
                // Debit increases Assets & Expenses
                if (in_array($account->account_type, ['asset', 'expense'])) {
                    $account->increment('current_balance', $amount);
                } else {
                    $account->decrement('current_balance', $amount);
                }
            } else {
                // Credit increases Liabilities, Equity & Revenue
                if (in_array($account->account_type, ['asset', 'expense'])) {
                    $account->decrement('current_balance', $amount);
                } else {
                    $account->increment('current_balance', $amount);
                }
            }
        }
    }
}
