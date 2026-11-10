<?php

namespace App\Services\Loans;

use App\Models\Account;
use App\Models\GuaranteeLock;
use App\Models\Loan;
use App\Models\LoanGuarantor;
use App\Models\Member;
use App\Services\Accounting\AccountBalanceService;
use DomainException;
use Illuminate\Support\Facades\DB;

class GuaranteeService
{
    private const CHANGEABLE = ['pending_guarantor_approval', 'pending'];

    public function __construct(private AccountBalanceService $balances)
    {
    }

    /** Guarantor accepts: lock their deposits, mark accepted, move the loan on if fully covered. */
    public function accept(Loan $loan, Member $guarantorMember): LoanGuarantor
    {
        return DB::transaction(function () use ($loan, $guarantorMember) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if ($loan->status !== 'pending_guarantor_approval') {
                throw new DomainException('This loan is no longer waiting for guarantor responses.');
            }

            $g = LoanGuarantor::where('loan_id', $loan->id)
                ->where('guarantor_member_id', $guarantorMember->id)
                ->lockForUpdate()
                ->first();

            if (!$g) {
                throw new DomainException('You are not a guarantor for this loan.');
            }
            if ($g->status !== 'pending') {
                throw new DomainException('You have already responded to this request.');
            }

            $account = $this->depositsAccount($guarantorMember->id);
            if (!$account) {
                throw new DomainException('You have no active deposits account to guarantee with.');
            }

            $this->balances->hold($account->id, $g->guaranteed_amount);

            GuaranteeLock::create([
                'loan_id'             => $loan->id,
                'loan_guarantor_id'   => $g->id,
                'guarantor_member_id' => $guarantorMember->id,
                'account_id'          => $account->id,
                'amount'              => $g->guaranteed_amount,
                'status'              => 'active',
            ]);

            $g->update(['status' => 'accepted', 'response_date' => now()]);

            $this->syncLoanStatus($loan);

            return $g->refresh();
        });
    }

    /** Guarantor declines. The loan keeps waiting; the applicant must add a replacement. */
    public function reject(Loan $loan, Member $guarantorMember, ?string $comments = null): LoanGuarantor
    {
        return DB::transaction(function () use ($loan, $guarantorMember, $comments) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if ($loan->status !== 'pending_guarantor_approval') {
                throw new DomainException('This loan is no longer waiting for guarantor responses.');
            }

            $g = LoanGuarantor::where('loan_id', $loan->id)
                ->where('guarantor_member_id', $guarantorMember->id)
                ->lockForUpdate()
                ->first();

            if (!$g) {
                throw new DomainException('You are not a guarantor for this loan.');
            }
            if ($g->status !== 'pending') {
                throw new DomainException('You have already responded to this request.');
            }

            $g->update(['status' => 'rejected', 'response_date' => now(), 'comments' => $comments]);

            return $g->refresh();
        });
    }

    /** Add (or re-request) a guarantor before the loan is approved. */
    public function add(Loan $loan, int $memberId, float $amount): LoanGuarantor
    {
        return DB::transaction(function () use ($loan, $memberId, $amount) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if (!in_array($loan->status, self::CHANGEABLE, true)) {
                throw new DomainException('Guarantors can only be changed before the loan is approved.');
            }
            if ($memberId === (int) $loan->member_id) {
                throw new DomainException('The applicant cannot be their own guarantor.');
            }
            if ($amount <= 0) {
                throw new DomainException('Guaranteed amount must be greater than zero.');
            }

            $member = Member::where('membership_status', 'active')->find($memberId);
            if (!$member) {
                throw new DomainException('The guarantor must be an active member.');
            }

            $existing = LoanGuarantor::where('loan_id', $loan->id)->where('guarantor_member_id', $memberId)->first();
            if ($existing && $existing->status !== 'rejected') {
                throw new DomainException('This member is already a guarantor on the loan.');
            }

            $available = (float) $member->accounts()->where('account_type', 'share_deposits')->sum('available_balance');
            if ($available + 0.005 < $amount) {
                throw new DomainException(
                    trim($member->first_name . ' ' . $member->last_name) .
                    ' does not have enough available deposits to guarantee KES ' . number_format($amount, 2) . '.'
                );
            }

            if ($existing) {
                $existing->update([
                    'guaranteed_amount' => $amount,
                    'status'            => 'pending',
                    'response_date'     => null,
                    'comments'          => null,
                ]);
                $g = $existing;
            } else {
                $g = LoanGuarantor::create([
                    'loan_id'             => $loan->id,
                    'guarantor_member_id' => $memberId,
                    'guaranteed_amount'   => $amount,
                    'status'              => 'pending',
                ]);
            }

            $this->syncLoanStatus($loan);

            return $g->refresh();
        });
    }

    /** Remove a guarantor before approval; releases their lock.*/
    public function remove(Loan $loan, LoanGuarantor $guarantor): void
    {
        DB::transaction(function () use ($loan, $guarantor) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if (!in_array($loan->status, self::CHANGEABLE, true)) {
                throw new DomainException('Guarantors can only be changed before the loan is approved.');
            }

            $g = LoanGuarantor::whereKey($guarantor->id)->lockForUpdate()->firstOrFail();

            if ((int) $g->loan_id !== (int) $loan->id) {
                throw new DomainException('That guarantor does not belong to this loan.');
            }

            $lock = GuaranteeLock::where('loan_guarantor_id', $g->id)->where('status', 'active')->lockForUpdate()->first();
            if ($lock) {
                $this->releaseLock($lock, 'Guarantor removed before approval');
            }

            $g->update(['status' => 'rejected', 'response_date' => now(), 'comments' => 'Removed from the loan']);

            $this->syncLoanStatus($loan);
        });
    }

    /** Release every active lock on a loan: on rejection, completion, write-off or top-up settlement. */
    public function releaseForLoan(Loan $loan, string $reason): void
    {
        DB::transaction(function () use ($loan, $reason) {
            GuaranteeLock::where('loan_id', $loan->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get()
                ->each(fn ($lock) => $this->releaseLock($lock, $reason));
        });
    }

    /** Keep the loan's status consistent with its guarantors. */
    public function syncLoanStatus(Loan $loan): void
    {
        $pending  = $loan->guarantors()->where('status', 'pending')->count();
        $accepted = (float) $loan->guarantors()->where('status', 'accepted')->sum('guaranteed_amount');
        $covered  = $accepted + 0.005 >= (float) $loan->applied_amount;

        if ($loan->status === 'pending_guarantor_approval' && $pending === 0 && $covered) {
            $loan->transitionTo('pending');
        } elseif ($loan->status === 'pending' && !$covered) {
            $loan->transitionTo('pending_guarantor_approval');
        }
    }

    private function releaseLock(GuaranteeLock $lock, string $reason): void
    {
        $this->balances->releaseHold($lock->account_id, $lock->amount);

        $lock->update([
            'status'         => 'released',
            'released_at'    => now(),
            'release_reason' => $reason,
        ]);
    }

    private function depositsAccount(int $memberId): ?Account
    {
        return Account::where('member_id', $memberId)
            ->where('account_type', 'share_deposits')
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }
}