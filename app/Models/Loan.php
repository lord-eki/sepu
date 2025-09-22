<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Loan extends Model
{
     use HasFactory;

    protected $fillable = [
        'loan_number', 'member_id', 'loan_product_id', 'applied_amount',
        'approved_amount', 'disbursed_amount', 'interest_rate', 'term_months',
        'monthly_repayment', 'total_repayable', 'processing_fee', 'insurance_fee',
        'purpose', 'status', 'application_date', 'approval_date',
        'disbursement_date', 'first_repayment_date', 'maturity_date',
        'approved_by', 'disbursed_by', 'approval_notes', 'rejection_reason',
        'documents', 'outstanding_balance', 'principal_balance',
        'interest_balance', 'penalty_balance', 'days_in_arrears'
    ];

    protected $casts = [
        'applied_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'disbursed_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_repayment' => 'decimal:2',
        'total_repayable' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'application_date' => 'date',
        'approval_date' => 'date',
        'disbursement_date' => 'date',
        'first_repayment_date' => 'date',
        'maturity_date' => 'date',
        'documents' => 'array',
        'outstanding_balance' => 'decimal:2',
        'principal_balance' => 'decimal:2',
        'interest_balance' => 'decimal:2',
        'penalty_balance' => 'decimal:2',
    ];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function loanProduct()
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function guarantors()
    {
        return $this->hasMany(LoanGuarantor::class);
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function disbursedBy()
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function paymentVouchers()
    {
        return $this->hasMany(PaymentVoucher::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('days_in_arrears', '>', 0);
    }
}
