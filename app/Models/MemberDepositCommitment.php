<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class MemberDepositCommitment extends Model
{
    use HasFactory;

    protected $table = 'member_deposit_commitments';

    protected $fillable = [
        'member_id',
        'type',
        'account_id',
        'loan_id',
        'monthly_amount',
        'deduction_day',
        'dividend_mode',
        'effective_from',
        'effective_to',
        'is_active',
        'notes',
        'set_by',
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'deduction_day'  => 'integer',
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'is_active'      => 'boolean',
    ];


    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function setBy()
    {
        return $this->belongsTo(User::class, 'set_by');
    }

    public function scopeActive($query)
    {
        $today = now()->toDateString();

        return $query
            ->where('is_active', true)
            ->where('effective_from', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $today);
            });
    }

    public function scopeForMember($query, int $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeForAccountType($query, string $type)
    {
        return $query->where('account_type', $type);
    }


    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->effective_from->toDateString() > $today) {
            return false;
        }

        if ($this->effective_to && $this->effective_to->toDateString() < $today) {
            return false;
        }

        return true;
    }
}