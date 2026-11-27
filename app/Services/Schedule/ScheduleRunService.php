<?php

namespace App\Services\Schedule;

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\MemberFinanceConfig;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Accounting\AccountBalanceService;
use App\Services\Accounting\LedgerService;
use App\Services\Loans\GuaranteeService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleRunService
{
    public function __construct(
        private AccountBalanceService $balances,
        private LedgerService $ledger,
        private GuaranteeService $guarantees,
    ) {}

    // Builds the idempotency key for a member's scheduled deposit in a month.
    public static function depositKey(int $memberId, string $month): string
    {
        return "sched:deposit:{$memberId}:{$month}";
    }

    // Builds the idempotency key for an instalment's scheduled deduction in a month.
    public static function repaymentKey(int $repaymentId, string $month): string
    {
        return "sched:repay:{$repaymentId}:{$month}";
    }

    // Returns the member ids, as keys, that already have a completed scheduled deposit in the month.
    public function depositMemberIdsDone(string $month): Collection
    {
        $start = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return Transaction::query()
            ->where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->where(function ($query) use ($month, $start, $end) {
                $query->where('idempotency_key', 'like', 'sched:deposit:%:' . $month . '%')
                    ->orWhere(function ($legacy) use ($start, $end) {
                        $legacy->where('transaction_id', 'like', 'DEP-SCH-%')
                            ->whereBetween('processed_at', [$start, $end]);
                    });
            })
            ->pluck('member_id')
            ->unique()
            ->flip();
    }

    // Returns the repayment ids that already had a scheduled deduction in the month.
    public function repaymentIdsRunForMonth(iterable $repaymentIds, string $month): array
    {
        $keys = collect($repaymentIds)->mapWithKeys(
            fn($id) => [self::repaymentKey((int) $id, $month) => (int) $id]
        );

        if ($keys->isEmpty()) {
            return [];
        }

        return Transaction::whereIn('idempotency_key', $keys->keys()->all())
            ->pluck('idempotency_key')
            ->map(fn($key) => $keys[$key])
            ->all();
    }

    // Counts the scheduled loan deductions already posted for the month, whatever their instalment status is now.
    public function repaymentRunCount(string $month): int
    {
        return Transaction::where('transaction_type', 'loan_repayment')
            ->where('idempotency_key', 'like', 'sched:repay:%:' . $month)
            ->count();
    }

    // Posts one member's monthly contribution and its journal entry.
    public function postMonthlyDeposit(int $memberId, string $month, User $by): array
    {
        return DB::transaction(function () use ($memberId, $month, $by) {
            if ($this->depositMemberIdsDone($month)->has($memberId)) {
                return ['status' => 'skipped', 'amount' => 0.0];
            }

            $config = $this->contributionConfig($memberId);
            $amount = round((float) $config->monthly_contribution, 2);

            $transaction = $this->balances->credit($config->contributionAccount->id, $amount, [
                'transaction_type' => 'deposit',
                'description' => 'Schedule: Monthly deposit - ' . $this->monthLabel($month),
                'payment_method' => config('sepu.schedule_deposit_method') ?: 'bank_transfer',
                'processed_by' => $by->id,
                'created_by' => $by->id,
                'idempotency_key' => $this->freshDepositKey($memberId, $month),
                'metadata' => ['schedule' => 'monthly_deposits', 'month' => $month],
            ]);

            $this->balances->postLedgerFor($transaction, $by->id);

            return ['status' => 'posted', 'amount' => $amount];
        });
    }

    // Deducts one loan instalment from the member's deposits and records every side of it.
    public function postLoanRepayment(int $repaymentId, float $requested, string $month, User $by): array
    {
        return DB::transaction(function () use ($repaymentId, $requested, $month, $by) {
            $repayment = LoanRepayment::whereKey($repaymentId)->lockForUpdate()->firstOrFail();
            $loan = Loan::whereKey($repayment->loan_id)->lockForUpdate()->firstOrFail();
            $key = self::repaymentKey($repayment->id, $month);

            if (Transaction::where('idempotency_key', $key)->exists()) {
                return ['status' => 'skipped', 'amount' => 0.0, 'completed' => false];
            }

            $this->assertRepayable($loan, $repayment);

            $deposits = $this->depositsAccount($loan);
            $amount = round(min($requested, (float) $repayment->outstanding_amount, (float) $deposits->available_balance), 2);

            if ($amount <= 0) {
                throw new DomainException(
                    'Insufficient available deposits (KES ' . number_format((float) $deposits->available_balance, 2) .
                        '). Amounts locked as guarantees cannot be deducted.'
                );
            }

            $split = $this->allocate($repayment, $amount, $loan);
            $description = "Schedule: Loan repayment - {$loan->loan_number} (" . $this->monthLabel($month) . ')';
            $metadata = array_merge($split, ['loan_id' => $loan->id, 'repayment_id' => $repayment->id, 'month' => $month]);

            $depositTransaction = $this->debitDeposits($deposits, $amount, $loan, $description, $key, $metadata, $by);
            $this->reduceLoanAccount($loan, $split['principal'], $description, $key, $metadata, $by);
            $this->postRepaymentJournal($depositTransaction, $loan, $amount, $split, $description, $key, $by);
            $this->applyToSchedule($repayment, $loan, $amount, $split, $depositTransaction);

            return ['status' => 'posted', 'amount' => $amount, 'completed' => $this->closeIfSettled($loan)];
        });
    }

    // Loads the member's active contribution setup or explains why there is none.
    private function contributionConfig(int $memberId): MemberFinanceConfig
    {
        $config = MemberFinanceConfig::activeContributions()
            ->where('member_id', $memberId)
            ->with(['member', 'contributionAccount'])
            ->first();

        if (! $config || ! $config->member || $config->member->membership_status !== 'active') {
            throw new DomainException('Member has no active monthly contribution set up.');
        }

        $account = $config->contributionAccount;

        if (! $account || ! $account->is_active || (int) $account->member_id !== $memberId) {
            throw new DomainException('The configured contribution account is missing or inactive.');
        }

        return $config;
    }

    // Returns an unused deposit key, adding a suffix when an earlier scheduled deposit was reversed.
    private function freshDepositKey(int $memberId, string $month): string
    {
        $base = self::depositKey($memberId, $month);
        $used = Transaction::where('idempotency_key', 'like', $base . '%')->count();

        return $used === 0 ? $base : "{$base}:r{$used}";
    }

    // Formats a Y-m month as a readable label.
    private function monthLabel(string $month): string
    {
        return Carbon::createFromFormat('!Y-m', $month)->format('F Y');
    }

    // Stops repayment on loans that are not running or instalments that are already settled.
    private function assertRepayable(Loan $loan, LoanRepayment $repayment): void
    {
        if (! in_array($loan->status, Loan::RUNNING_STATUSES, true)) {
            throw new DomainException("Loan {$loan->loan_number} is not running (status: {$loan->status}).");
        }

        if ($repayment->status === 'paid' || (float) $repayment->outstanding_amount <= 0) {
            throw new DomainException('This instalment is already settled.');
        }
    }

    // Locks and returns the member's active deposits account.
    private function depositsAccount(Loan $loan): Account
    {
        $account = Account::where('member_id', $loan->member_id)
            ->where('account_type', 'share_deposits')
            ->where('is_active', true)
            ->orderBy('id')
            ->lockForUpdate()
            ->first();

        if (! $account) {
            throw new DomainException('Member has no active deposits account to deduct from.');
        }

        return $account;
    }

    // Splits a payment into penalty, then interest, then principal, allowing for earlier partial payments.
    private function allocate(LoanRepayment $repayment, float $amount, Loan $loan): array
    {
        $paid = (float) $repayment->paid_amount;
        $penaltyLeft = max(0, (float) $repayment->penalty_amount - $paid);
        $afterPenalty = max(0, $paid - (float) $repayment->penalty_amount);
        $interestLeft = max(0, (float) $repayment->interest_amount - $afterPenalty);
        $afterInterest = max(0, $afterPenalty - (float) $repayment->interest_amount);
        $principalLeft = max(0, (float) $repayment->principal_amount - $afterInterest);

        $penalty = round(min($amount, $penaltyLeft), 2);
        $interest = round(min($amount - $penalty, $interestLeft), 2);
        $principal = round(min($amount - $penalty - $interest, $principalLeft), 2);
        $leftover = round($amount - $penalty - $interest - $principal, 2);

        if (abs($leftover) > 0.01) {
            throw new DomainException("Instalment components do not add up to the amount due for loan {$loan->loan_number}. Check this schedule row.");
        }

        return ['penalty' => $penalty, 'interest' => $interest, 'principal' => round($principal + $leftover, 2)];
    }

    // Takes the payment out of the member's deposits.
    private function debitDeposits(Account $deposits, float $amount, Loan $loan, string $description, string $key, array $metadata, User $by): Transaction
    {
        return $this->balances->debit($deposits->id, $amount, [
            'transaction_type' => 'loan_repayment',
            'description' => $description,
            'reference_number' => $loan->loan_number,
            'payment_method' => 'system_transfer',
            'processed_by' => $by->id,
            'created_by' => $by->id,
            'idempotency_key' => $key,
            'metadata' => $metadata,
        ]);
    }

    // Reduces the outstanding principal held on the member's loan account.
    private function reduceLoanAccount(Loan $loan, float $principal, string $description, string $key, array $metadata, User $by): void
    {
        if ($principal <= 0) {
            return;
        }

        $account = Account::where('member_id', $loan->member_id)
            ->where('account_type', 'loan_outstanding')
            ->lockForUpdate()
            ->first();

        if (! $account) {
            throw new DomainException('The member has no loan account to reduce.');
        }

        $this->balances->debit($account->id, $principal, [
            'transaction_type' => 'loan_repayment',
            'description' => $description,
            'reference_number' => $loan->loan_number,
            'payment_method' => 'system_transfer',
            'processed_by' => $by->id,
            'created_by' => $by->id,
            'idempotency_key' => "{$key}:principal",
            'metadata' => $metadata,
        ]);
    }

    // Posts Dr member deposits against Cr loans, interest income and penalty income.
    private function postRepaymentJournal(Transaction $transaction, Loan $loan, float $amount, array $split, string $description, string $key, User $by): void
    {
        $lines = [['account' => 'member_deposits', 'type' => 'debit', 'amount' => $amount, 'description' => $description]];

        foreach (['principal' => 'loans_performing', 'interest' => 'interest_income', 'penalty' => 'penalty_income'] as $part => $role) {
            if ($split[$part] > 0) {
                $lines[] = ['account' => $role, 'type' => 'credit', 'amount' => $split[$part], 'description' => $description];
            }
        }

        $entry = $this->ledger->post($lines, $description, $loan, "{$key}:ledger", $by->id);

        $transaction->update(['journal_entry_id' => $entry->id]);
    }

    // Records the payment on the instalment and reduces the loan balances.
    private function applyToSchedule(LoanRepayment $repayment, Loan $loan, float $amount, array $split, Transaction $transaction): void
    {
        $outstanding = round(max(0, (float) $repayment->outstanding_amount - $amount), 2);

        $repayment->update([
            'paid_amount' => round((float) $repayment->paid_amount + $amount, 2),
            'outstanding_amount' => $outstanding,
            'status' => $outstanding <= 0.004 ? 'paid' : 'partial',
            'payment_date' => now()->toDateString(),
            'transaction_id' => $transaction->id,
        ]);

        $loan->update([
            'outstanding_balance' => max(0, round((float) $loan->outstanding_balance - $amount, 2)),
            'principal_balance' => max(0, round((float) $loan->principal_balance - $split['principal'], 2)),
            'interest_balance' => max(0, round((float) $loan->interest_balance - $split['interest'], 2)),
            'penalty_balance' => max(0, round((float) $loan->penalty_balance - $split['penalty'], 2)),
        ]);
    }

    // Completes the loan and releases guarantor locks once nothing is left to pay.
    private function closeIfSettled(Loan $loan): bool
    {
        $loan->refresh();

        $unpaid = LoanRepayment::where('loan_id', $loan->id)
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->exists();

        if ($unpaid || (float) $loan->outstanding_balance > 0.01) {
            return false;
        }

        $loan->transitionTo('completed', [
            'outstanding_balance' => 0,
            'principal_balance' => 0,
            'interest_balance' => 0,
            'penalty_balance' => 0,
        ]);

        $this->guarantees->releaseForLoan($loan, 'loan_completed');

        return true;
    }
}
