<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'voucher_type',
        'payee_name',
        'payee_phone',
        'payee_account',
        'amount',
        'purpose',
        'description',
        'budget_item_id',
        'loan_id',
        'status',
        'created_by',
        'approved_by',
        'paid_by',
        'approval_date',
        'payment_date',
        'approval_notes',
        'rejection_reason',
        'supporting_documents'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approval_date' => 'date',
        'payment_date' => 'date',
        'supporting_documents' => 'array',
    ];

    // Relationships
    public function budgetItem()
    {
        return $this->belongsTo(BudgetItem::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Add these methods to PaymentVoucher.php
    public function creator()
    {
        return $this->createdBy();
    }

    public function approver()
    {
        return $this->approvedBy();
    }

    public function payer()
    {
        return $this->paidBy();
    }
}
