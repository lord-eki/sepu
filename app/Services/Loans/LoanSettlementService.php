<?php

namespace App\Services\Loans;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\User;
use App\Services\Accounting\AccountBalanceService;
use App\Services\Accounting\LedgerService;
use App\Services\Audit\AuditRecorder;
use DomainException;

class LoanSettlementService
{
    public function __construct(
        private AccountBalanceService $balances,
        private LedgerService $ledger,
        private GuaranteeService $guarantees,
        private LoanAccountProvider $loanAccounts,
        private AuditRecorder $audit,
    ) {}

    // Works out what it takes to settle a loan today: penalty, principal and the interest that has fallen due.
    public function quote(Loan $loan): array
    {
        $penalty = 0.0;
        $interest = 0.0;
        $principal = 0.0;
        $waived = 0.0;
        $rows = [];

        $instalments = LoanRepayment::where('loan_id', $loan->id)
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->orderBy('due_date')
            ->get();

        foreach ($instalments as $instalment) {
            $left = InstallmentAllocator::split(
                (float) $instalment->outstanding_amount,
                (float) $instalment->penalty_amount,
                (float) $instalment->interest_amount,
                (float) $instalment->principal_amount,
                (float) $instalment->paid_amount,
            );

            $interestDue = $this->interestIsPayable($instalment) ? $left['interest'] : 0.0;

            $penalty += $left['penalty'];
            $principal += $left['principal'];
            $interest += $interestDue;
            $waived += $left['interest'] - $interestDue;

            $rows[] = [
                'id' => $instalment->id,
                'penalty' => $left['penalty'],
                'interest' => $interestDue,
                'principal' => $left['principal'],
                'waived_interest' => round($left['interest'] - $interestDue, 2),
            ];
        }

        return [
            'penalty' => round($penalty, 2),
            'interest' => round($interest, 2),
            'principal' => round($principal, 2),
            'waived_interest' => round($waived, 2),
            'total' => round($penalty + $interest + $principal, 2),
            'rows' => $rows,
        ];
    }

    // Returns the quote for the parent a top-up will settle, or null when the top-up keeps the parent running.
    public function quoteForTopUp(Loan $topUp): ?array
    {
        if (! $topUp->parent_loan_id || ! $topUp->loanProduct?->settle_existing_balance) {
            return null;
        }

        $parent = Loan::whereKey($topUp->parent_loan_id)->lockForUpdate()->firstOrFail();

        if (! in_array($parent->status, Loan::RUNNING_STATUSES, true)) {
            throw new DomainException("Parent loan {$parent->loan_number} is no longer running, so it cannot be settled.");
        }

        return array_merge($this->quote($parent), ['parent' => $parent]);
    }

    // Closes the parent loan from a top-up's proceeds and records every side of it.
    public function settle(Loan $parent, Loan $topUp, array $quote, User $by): void
    {
        $description = "Settlement of loan {$parent->loan_number} by top-up {$topUp->loan_number}";
        $key = "topup:{$topUp->id}:settlement";

        $this->markInstalmentsSettled($quote['rows']);
        $this->reduceLoanAccount($parent, $quote['principal'], $description, $key, $by);
        $this->postJournal($parent, $quote, $description, $key, $by);

        $parent->transitionTo('completed', [
            'outstanding_balance' => 0,
            'principal_balance' => 0,
            'interest_balance' => 0,
            'penalty_balance' => 0,
        ]);

        $this->guarantees->releaseForLoan($parent, 'top_up_settled');

        $this->audit->record('loan.top_up_settled', $parent, $by, [], [
            'top_up' => $topUp->loan_number,
            'principal' => $quote['principal'],
            'interest' => $quote['interest'],
            'penalty' => $quote['penalty'],
            'waived_interest' => $quote['waived_interest'],
        ]);
    }

    // Tells whether an instalment's interest is charged, which depends on the settlement interest policy.
    private function interestIsPayable(LoanRepayment $instalment): bool
    {
        return config('sepu.settlement_interest', 'accrued') === 'full' || $instalment->due_date->lte(today());
    }

    // Marks each settled instalment paid and drops any interest that is waived.
    private function markInstalmentsSettled(array $rows): void
    {
        foreach ($rows as $row) {
            $instalment = LoanRepayment::whereKey($row['id'])->lockForUpdate()->firstOrFail();
            $payable = round($row['penalty'] + $row['interest'] + $row['principal'], 2);

            $instalment->update([
                'interest_amount' => round((float) $instalment->interest_amount - $row['waived_interest'], 2),
                'expected_amount' => round((float) $instalment->expected_amount - $row['waived_interest'], 2),
                'paid_amount' => round((float) $instalment->paid_amount + $payable, 2),
                'outstanding_amount' => 0,
                'status' => 'paid',
                'payment_date' => now()->toDateString(),
            ]);
        }
    }

    // Takes the settled principal off the member's loan account.
    private function reduceLoanAccount(Loan $parent, float $principal, string $description, string $key, User $by): void
    {
        if ($principal <= 0) {
            return;
        }

        $this->balances->debit($this->loanAccounts->forMember($parent->member_id)->id, $principal, [
            'transaction_type' => 'loan_repayment',
            'description' => $description,
            'reference_number' => $parent->loan_number,
            'payment_method' => 'system_transfer',
            'processed_by' => $by->id,
            'created_by' => $by->id,
            'idempotency_key' => "{$key}:principal",
            'metadata' => ['loan_id' => $parent->id, 'settlement' => true],
        ]);
    }

    // Posts Dr top-up settlement against Cr loans, interest income and penalty income, on the parent loan.
    private function postJournal(Loan $parent, array $quote, string $description, string $key, User $by): void
    {
        $lines = [['account' => 'top_up_settlement', 'type' => 'debit', 'amount' => $quote['total'], 'description' => $description]];

        foreach (['principal' => 'loans_performing', 'interest' => 'interest_income', 'penalty' => 'penalty_income'] as $part => $role) {
            if ($quote[$part] > 0) {
                $lines[] = ['account' => $role, 'type' => 'credit', 'amount' => $quote[$part], 'description' => $description];
            }
        }

        $this->ledger->post($lines, $description, $parent, "{$key}:ledger", $by->id);
    }
}