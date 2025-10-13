<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MemberDividend extends Model
{
    use HasFactory;

    protected $fillable = [
        'dividend_id', 'member_id', 'shares_balance', 'dividend_amount',
        'status', 'payment_date', 'transaction_id'
    ];

    protected $casts = [
        'shares_balance' => 'decimal:2',
        'dividend_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relationships
    public function dividend()
    {
        return $this->belongsTo(Dividend::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
