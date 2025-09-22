<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
     use HasFactory;

    protected $fillable = [
        'member_id', 'account_number', 'account_type', 'balance',
        'available_balance', 'is_active', 'last_transaction_at'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'last_transaction_at' => 'datetime',
    ];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Scope for active accounts
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
