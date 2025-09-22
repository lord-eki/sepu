<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'account_id', 'member_id', 'transaction_type',
        'amount', 'balance_before', 'balance_after', 'description',
        'reference_number', 'payment_method', 'payment_reference',
        'status', 'processed_by', 'processed_at', 'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'processed_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function loanRepayment()
    {
        return $this->hasOne(LoanRepayment::class);
    }

    // Scope for completed transactions
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
