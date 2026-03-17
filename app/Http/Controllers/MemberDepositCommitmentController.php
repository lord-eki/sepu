<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberDepositCommitment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Manages per-member monthly deposit commitments.
 *
 * All routes are scoped under /members/{member}/deposit-commitments
 * as registered in web.php under the members.deposit-commitments.* names.
 *
 * A commitment record answers: "How much does this member deposit
 * each month, into which account type, and for what date range?"
 * The schedule engine reads these rows
 */
class MemberDepositCommitmentController extends Controller
{
    /**
     * List all commitments for a member (full history, all statuses).
     */
    public function index(Member $member)
    {
        $commitments = $member->depositCommitments()
            ->with('account', 'setBy')
            ->orderByDesc('effective_from')
            ->get()
            ->map(fn ($c) => $this->format($c));

        $savingsAccounts = $member->accounts()
            ->where('is_active', true)
            ->get(['id', 'account_number', 'account_type', 'balance']);

        return Inertia::render('Members/DepositCommitments/Index', [
            'member'          => $member,
            'commitments'     => $commitments,
            'savingsAccounts' => $savingsAccounts,
            'accountTypes'    => $this->accountTypeOptions(),
        ]);
    }

    /**
     * Create a new commitment for a member.
     *
     */
    public function store(Request $request, Member $member)
    {
        $data = $request->validate([
            'account_id'     => 'nullable|exists:accounts,id',
            'account_type'   => 'required|string|max:50',
            'monthly_amount' => 'required|numeric|min:1',
            'deduction_day'  => 'required|integer|min:1|max:28',
            'effective_from' => 'required|date',
            'effective_to'   => 'nullable|date|after:effective_from',
            'notes'          => 'nullable|string|max:500',
        ]);

        // Close any existing open-ended commitment for this account type
        $member->depositCommitments()
            ->where('account_type', $data['account_type'])
            ->where('is_active', true)
            ->whereNull('effective_to')
            ->update(['is_active' => false]);

        $commitment = $member->depositCommitments()->create(array_merge($data, [
            'is_active' => true,
            'set_by'    => Auth::id(),
        ]));

        if ($request->wantsJson()) {
            return response()->json([
                'message'    => 'Commitment created.',
                'commitment' => $this->format($commitment->load('account')),
            ]);
        }

        return back()->with('success',
            'Monthly deposit of KES ' . number_format($commitment->monthly_amount, 2) .
            ' set for ' . $member->full_name . '.'
        );
    }

    /**
     * Update an existing commitment.
     * Only amount, account link, deduction day, end date, active flag and notes
     * can be changed.  To change account_type or start date, create a new row.
     */
    public function update(Request $request, Member $member, MemberDepositCommitment $commitment)
    {
        $this->assertBelongsTo($member, $commitment);

        $data = $request->validate([
            'account_id'     => 'nullable|exists:accounts,id',
            'monthly_amount' => 'required|numeric|min:1',
            'deduction_day'  => 'required|integer|min:1|max:28',
            'effective_to'   => 'nullable|date|after:' . $commitment->effective_from->toDateString(),
            'is_active'      => 'boolean',
            'notes'          => 'nullable|string|max:500',
        ]);

        $commitment->update(array_merge($data, ['set_by' => Auth::id()]));

        if ($request->wantsJson()) {
            return response()->json([
                'message'    => 'Commitment updated.',
                'commitment' => $this->format($commitment->fresh()->load('account')),
            ]);
        }

        return back()->with('success', 'Deposit commitment updated.');
    }

    /**
     * Delete a commitment record entirely.

     */
    public function destroy(Member $member, MemberDepositCommitment $commitment)
    {
        $this->assertBelongsTo($member, $commitment);

        $commitment->delete();

        return back()->with('success', 'Deposit commitment removed.');
    }

    /**
     * Toggle is_active without deleting the record.
     */
    public function toggle(Member $member, MemberDepositCommitment $commitment)
    {
        $this->assertBelongsTo($member, $commitment);

        $commitment->update(['is_active' => !$commitment->is_active]);

        $state = $commitment->is_active ? 'activated' : 'paused';

        return back()->with('success', "Deposit commitment {$state}.");
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function assertBelongsTo(Member $member, MemberDepositCommitment $commitment): void
    {
        abort_if($commitment->member_id !== $member->id, 404);
    }

    /**
     * Serialise a commitment for Inertia props or JSON responses.
     */
    private function format(MemberDepositCommitment $c): array
    {
        return [
            'id'             => $c->id,
            'member_id'      => $c->member_id,
            'account_id'     => $c->account_id,
            'account_number' => $c->account?->account_number,
            'account_type'   => $c->account_type,
            'monthly_amount' => (float) $c->monthly_amount,
            'deduction_day'  => $c->deduction_day,
            'effective_from' => $c->effective_from?->format('Y-m-d'),
            'effective_to'   => $c->effective_to?->format('Y-m-d'),
            'is_active'      => $c->is_active,
            'is_current'     => $c->isCurrentlyActive(),
            'notes'          => $c->notes,
            'set_by_name'    => $c->setBy?->name,
            'updated_at'     => $c->updated_at?->format('Y-m-d'),
        ];
    }

    /**
     * Account type options for the form dropdown.
     * Keys must match the account_type values used in the accounts table.
     */
    private function accountTypeOptions(): array
    {
        return [
            'ordinary_savings' => 'Ordinary Savings',
            'holiday_savings'  => 'Holiday Savings',
            'shares'           => 'Share Capital',
            'fixed_deposit'    => 'Fixed Deposit',
        ];
    }
}