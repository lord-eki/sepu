<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class MemberFinanceConfig extends Model
{
    protected $fillable = [
        'member_id',
        'contribution_active',
        'monthly_contribution',
        'contribution_account_id',
        'loan_auto_deduct',
        'loan_deduction_amount',
        'dividend_eligible',
        'dividend_account_id',
    ];

    protected $table = 'member_finance_configs';

    protected $casts = [
        'contribution_active'    => 'boolean',
        'monthly_contribution'   => 'float',
        'loan_auto_deduct'       => 'boolean',
        'loan_deduction_amount'  => 'float',
        'dividend_eligible'      => 'boolean',
    ];


    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function contributionAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'contribution_account_id');
    }

    public function dividendAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'dividend_account_id');
    }


    public function scopeActiveContributions($query)
    {
        return $query->where('contribution_active', true)
                     ->where('monthly_contribution', '>', 0)
                     ->whereNotNull('contribution_account_id');
    }

    public function scopeAutoLoanDeduct($query)
    {
        return $query->where('loan_auto_deduct', true);
    }

    public function scopeDividendEligible($query)
    {
        return $query->where('dividend_eligible', true);
    }
}