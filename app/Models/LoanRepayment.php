<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id', 'transaction_id', 'due_date', 'expected_amount',
        'principal_amount', 'interest_amount', 'penalty_amount',
        'paid_amount', 'outstanding_amount', 'status', 'payment_date',
        'days_late'
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
        'expected_amount' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
    ];

    // Relationships
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Scopes
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeDueToday($query)
    {
        return $query->where('due_date', now()->toDateString())
                    ->where('status', 'pending');
    }
}
