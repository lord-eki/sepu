<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'chart_of_account_id',  
        'category',             
        'item_name',           
        'description',
        'budgeted_amount',
        'spent_amount',
        'remaining_amount',
    ];

    protected $casts = [
        'budgeted_amount'  => 'decimal:2',
        'spent_amount'     => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];


    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * The COA leaf account this budget line tracks.
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function paymentVouchers()
    {
        return $this->hasMany(PaymentVoucher::class);
    }


 
    public function getDisplayNameAttribute(): string
    {
        if ($this->chartOfAccount) {
            return $this->chartOfAccount->account_code . ' – ' . $this->chartOfAccount->account_name;
        }
        return $this->item_name ?? $this->category ?? 'Unnamed Item';
    }

  
    public function getCategoryLabelAttribute(): string
    {
        if ($this->chartOfAccount?->parentAccount) {
            $parts  = [];
            $cursor = $this->chartOfAccount->parentAccount;
            while ($cursor) {
                array_unshift($parts, $cursor->account_name);
                $cursor = $cursor->parentAccount;
            }
            return implode(' > ', $parts);
        }
        return $this->category ?? '';
    }


    public function recalculateRemaining(): void
    {
        $this->remaining_amount = (float)$this->budgeted_amount - (float)$this->spent_amount;
        $this->save();
    }
}