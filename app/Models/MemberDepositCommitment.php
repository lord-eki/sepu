<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 *
 * Stores the monthly deposit amount a member has committed to contribute
 * for a given account type.
 *
 * The ScheduleController::runMonthlyDeposits() method calls
 * MemberDepositCommitment::active()->with('member', 'account')->get()
 * to build its deposit list at run-time, replacing any hard-coded amount.
 *
 */
class MemberDepositCommitment extends Model
{
    use HasFactory;

    protected $table = 'member_deposit_commitments';

    protected $fillable = [
        'member_id',
        'account_id',
        'account_type',
        'monthly_amount',
        'deduction_day',
        'effective_from',
        'effective_to',
        'is_active',
        'set_by',
        'notes',
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'deduction_day'  => 'integer',
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'is_active'      => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /** The savings account that receives the monthly credit. */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /** Staff member who created / last modified this commitment. */
    public function setBy()
    {
        return $this->belongsTo(User::class, 'set_by');
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    /**
     * Active commitments within their effective date range.
     * Primary scope used by the schedule engine.
     */
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

    // ── Helpers ──────────────────────────────────────────────────────────

    /** Returns true if this commitment should be processed right now. */
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