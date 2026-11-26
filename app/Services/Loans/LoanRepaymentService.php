<?php

namespace App\Services\Loans;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\User;
use App\Services\Accounting\AccountBalanceService;
use App\Services\Accounting\LedgerService;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LoanRepaymentService
{
    public function __construct(
        private AccountBalanceService $balances,
        private LedgerService $ledger,
        private GuaranteeService $guarantees,
    ) {}

    // Records a staff-entered repayment against the oldest unpaid instalments and posts every side of it.
    public function record(Loan $loan, float $amount, string $source, string $requestKey, User $by, ?string $reference = null): array
    {
        return DB::transaction(function () use ($loan, $amount, $source, $requestKey, $by, $reference) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();
            $key = "loan-repay:{$loan->id}:{$requestKey}";

            if (JournalEntry::where('idempotency_key', "{$key}:ledger")->exists()) {
                return ['status' => 'duplicate', 'amount' => 0.0, 'completed' => false, 'split' => []];
            }

            $this->assertRunning($loan);

            $instalments = LoanRepayment::where('loan_id', $loan->id)
                ->whereIn('status', ['pending', 'overdue', 'partial'])
                ->orderBy('due_date')
                ->lockForUpdate()
                ->get();

            $amount = round($amount, 2);
            $this->assertWithinOwed($instalments, $amount);

            $split = $this->applyToInstalments($instalments, $amount, $loan);
            $description = "Loan repayment - {$loan->loan_number}" . ($reference ? " ({$reference})" : '');
            $metadata = array_merge($split, ['loan_id' => $loan->id, 'source' => $source, 'reference' => $reference]);

            $this->takePayment($loan, $amount, $source, $description, $key, $metadata, $by);
            $this->reduceLoanAccount($loan, $split['principal'], $source, $description, $key, $metadata, $by);
            $this->postJournal($loan, $amount, $source, $split, $description, $key, $by);
            $this->reduceLoanBalances($loan, $amount, $split);

            return ['status' => 'posted', 'amount' => $amount, 'completed' => $this->closeIfSettled($loan), 'split' => $split];
        });
    }

    // Stops repayment on loans that are not running.
    private function assertRunning(Loan $loan): void
    {
        if (! in_array($loan->status, Loan::RUNNING_STATUSES, true)) {
            throw new DomainException("Loan {$loan->loan_number} is not running (status: {$loan->status}).");
        }
    }

    // Stops payments that are zero or larger than everything still owed on the schedule.
    private function assertWithinOwed(Collection $instalments, float $amount): void
    {
        $owed = round((float) $instalments->sum('outstanding_amount'), 2);

        if ($amount <= 0) {
            throw new DomainException('The repayment amount must be greater than zero.');
        }

        if ($amount > $owed + 0.005) {
            throw new DomainException('The amount exceeds the KES ' . number_format($owed, 2) . ' still owed on this loan.');
        }
    }

    // Spreads the payment over the oldest instalments first and returns the penalty, interest and principal totals.
    private function applyToInstalments(Collection $instalments, float $amount, Loan $loan): array
    {
        $totals = ['penalty' => 0.0, 'interest' => 0.0, 'principal' => 0.0];
        $remaining = $amount;

        foreach ($instalments as $instalment) {
            if ($remaining <= 0) {
                break;
            }

            $payment = round(min($remaining, (float) $instalment->outstanding_amount), 2);

            $part = InstallmentAllocator::split(
                $payment,
                (float) $instalment->penalty_amount,
                (float) $instalment->interest_amount,
                (float) $instalment->principal_amount,
                (float) $instalment->paid_amount,
            );

            if (abs($part['unallocated']) > 0.01) {
                throw new DomainException("Instalment components do not add up to the amount due for loan {$loan->loan_number}. Check the schedule.");
            }

            $outstanding = round((float) $instalment->outstanding_amount - $payment, 2);

            $instalment->update([
                'paid_amount' => round((float) $instalment->paid_amount + $payment, 2),
                'outstanding_amount' => $outstanding,
                'status' => $outstanding <= 0.004 ? 'paid' : 'partial',
                'payment_date' => now()->toDateString(),
            ]);

            foreach (['penalty', 'interest', 'principal'] as $component) {
                $totals[$component] = round($totals[$component] + $part[$component], 2);
            }

            $remaining = round($remaining - $payment, 2);
        }

        return $totals;
    }

    // Takes the money out of the member's deposits when the repayment is paid from savings.
    private function takePayment(Loan $loan, float $amount, string $source, string $description, string $key, array $metadata, User $by): void
    {
        if ($source !== 'deposits') {
            return;
        }

        $deposits = Account::where('member_id', $loan->member_id)
            ->where('account_type', 'share_deposits')
            ->where('is_active', true)
            ->orderBy('id')
            ->lockForUpdate()
            ->first();

        if (! $deposits) {
            throw new DomainException('Member has no active deposits account to pay from.');
        }

        if ((float) $deposits->available_balance + 0.005 < $amount) {
            throw new DomainException('Insufficient available deposits (KES ' . number_format((float) $deposits->available_balance, 2) . ').');
        }

        $this->balances->debit($deposits->id, $amount, [
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
    private function reduceLoanAccount(Loan $loan, float $principal, string $source, string $description, string $key, array $metadata, User $by): void
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
            'payment_method' => $source === 'deposits' ? 'system_transfer' : $source,
            'processed_by' => $by->id,
            'created_by' => $by->id,
            'idempotency_key' => "{$key}:principal",
            'metadata' => $metadata,
        ]);
    }

    // Posts Dr deposits, cash or bank against Cr loans, interest income and penalty income.
    private function postJournal(Loan $loan, float $amount, string $source, array $split, string $description, string $key, User $by): void
    {
        $lines = [['account' => $this->receivingRole($source), 'type' => 'debit', 'amount' => $amount, 'description' => $description]];

        foreach (['principal' => 'loans_performing', 'interest' => 'interest_income', 'penalty' => 'penalty_income'] as $part => $role) {
            if ($split[$part] > 0) {
                $lines[] = ['account' => $role, 'type' => 'credit', 'amount' => $split[$part], 'description' => $description];
            }
        }

        $this->ledger->post($lines, $description, $loan, "{$key}:ledger", $by->id);
    }

    // Maps where the money came from to the ledger account that receives it.
    private function receivingRole(string $source): string
    {
        if ($source === 'deposits') {
            return 'member_deposits';
        }

        $role = config("sepu_ledger.cash_by_method.{$source}");

        if (! $role) {
            throw new DomainException("Payment source '{$source}' has no ledger account.");
        }

        return $role;
    }

    // Reduces the loan's outstanding, principal, interest and penalty balances.
    private function reduceLoanBalances(Loan $loan, float $amount, array $split): void
    {
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