<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'min_amount',
        'max_amount',
        'interest_rate',
        'min_term_months',
        'max_term_months',
        'processing_fee_rate',
        'insurance_rate',
        'grace_period_days',
        'penalty_rate',
        'eligibility_criteria',
        'required_documents',
        'requires_guarantor',
        'min_guarantors',
        'is_active',
        'interest_method',
        'allow_top_up',
        'top_up_eligibility_percent',
        'max_top_ups',
        'settle_existing_balance',
        'max_top_up_amount',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'processing_fee_rate' => 'decimal:2',
        'insurance_rate' => 'decimal:2',
        'penalty_rate' => 'decimal:2',
        'eligibility_criteria' => 'array',
        'required_documents' => 'array',
        'requires_guarantor' => 'boolean',
        'is_active' => 'boolean',
        'allow_top_up' => 'boolean',
        'settle_existing_balance' => 'boolean',
        'top_up_eligibility_percent' => 'decimal:2',
        'max_top_up_amount' => 'decimal:2',
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
