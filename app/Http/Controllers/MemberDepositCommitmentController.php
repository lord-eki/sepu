<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberDepositCommitmentRequest;
use App\Http\Requests\UpdateMemberDepositCommitmentRequest;
use App\Models\Member;
use App\Models\Loan;
use App\Models\MemberDepositCommitment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MemberDepositCommitmentController extends Controller
{
    /**
     * Display finance setup page for a member
     */
    public function index(Member $member)
    {
        $commitments = $member->depositCommitments()
            ->with(['account', 'setBy', 'loan'])
            ->latest()
            ->get()
            ->map(fn($c) => $this->format($c));

        return Inertia::render('Members/DepositCommitments/Index', [
            'member'      => $member,
            'commitments' => $commitments,
            'accounts'    => $member->accounts()->where('is_active', true)->get(),
            'loans'       => $member->loans()->where('status', 'Active')->get(),
            'types'       => $this->types(),
        ]);
    }

    /**
     * Store new finance setup configuration
     */
    public function store(StoreMemberDepositCommitmentRequest $request, Member $member)
    {
        $data = $request->validated();
        $member->depositCommitments()->where('type', $data['type'])->where('is_active', true)
            ->update([
                'is_active' => false,
                'effective_to' => now()
            ]);

        $member->depositCommitments()->create([
            'type' => $data['type'],
            'account_id' => $data['account_id'] ?? null,
            'loan_id'    => $data['loan_id'] ?? null,
            'monthly_amount'  => $data['monthly_amount'],
            'principal_amount' => $data['principal_amount'] ?? null,
            'interest_amount' => $data['interest_amount'] ?? null,
            'deduction_day' => $data['deduction_day'] ?? null,
            'dividend_mode' => $data['dividend_mode'] ?? null,
            'effective_from' => $data['effective_from'],
            'effective_to'   => $data['effective_to'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
            'set_by' => Auth::id(),
        ]);

        return back()->with('success', 'Finance setup saved successfully.');
    }

    /**
     * Update existing setup
     */
    public function update(UpdateMemberDepositCommitmentRequest $request, Member $member, MemberDepositCommitment $commitment)
    {
        $this->assertBelongsTo($member, $commitment);

        $data = $request->validated();

        $commitment->update(array_merge($data, [
            'set_by' => Auth::id()
        ]));

        return back()->with('success', 'Finance setup updated successfully.');
    }

    /**
     * Delete setup
     */
    public function destroy(Member $member, MemberDepositCommitment $commitment)
    {
        $this->assertBelongsTo($member, $commitment);

        $commitment->delete();

        return back()->with('success', 'Finance setup deleted successfully.');
    }

    /**
     * Toggle active/inactive
     */
    public function toggle(Member $member, MemberDepositCommitment $commitment)
    {
        $this->assertBelongsTo($member, $commitment);

        $commitment->update([
            'is_active' => !$commitment->is_active
        ]);

        return back()->with('success', 'Setup status updated successfully.');
    }


    /**
     * Ensure commitment belongs to member
     */
    private function assertBelongsTo(Member $member, MemberDepositCommitment $commitment)
    {
        abort_if($commitment->member_id !== $member->id, 404);
    }

    /**
     * Format response for frontend
     */
    private function format(MemberDepositCommitment $c): array
    {
        return [
            'id' => $c->id,
            'type' => $c->type,
            'monthly_amount'   => (float) $c->monthly_amount,
            'principal_amount' => (float) $c->principal_amount,
            'interest_amount'  => (float) $c->interest_amount,
            'deduction_day' => $c->deduction_day,
            'account_id'     => $c->account_id,
            'account_number' => $c->account?->account_number,
            'loan_id'   => $c->loan_id,
            'loan_name' => $c->loan?->loan_product?->name,
            'dividend_mode' => $c->dividend_mode,
            'effective_from' => optional($c->effective_from)->format('Y-m-d'),
            'effective_to'   => optional($c->effective_to)->format('Y-m-d'),
            'is_active' => $c->is_active,
            'notes'     => $c->notes,
            'set_by'    => $c->setBy?->name,
        ];
    }

    /**
     * Finance setup types
     */
    private function types(): array
    {
        return [
            'contribution'   => 'Monthly Contribution',
            'loan_repayment' => 'Loan Repayment',
            'dividend'       => 'Dividend Setup',
        ];
    }
}
