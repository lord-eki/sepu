<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id', 'category', 'item_name', 'description',
        'budgeted_amount', 'spent_amount', 'remaining_amount'
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    // Relationships
    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function paymentVouchers()
    {
        return $this->hasMany(PaymentVoucher::class);
    }
}
