<?php

namespace App\Services\Loans;

use App\Models\Loan;
use App\Models\JournalEntry;
use App\Services\Accounting\LedgerService;

class LoanStatementService
{
    public function __construct(private LedgerService $ledger) {}

    // Builds a loan statement from the ledger: summary, schedule and every disbursement and repayment.
    public function build(Loan $loan): array
    {
        $loan->loadMissing(['member', 'loanProduct', 'repayments']);

        $entries = $this->entries($loan);

        return [
            'loan' => $this->header($loan),
            'summary' => $this->summary($loan, $entries),
            'instalments' => $loan->repayments->sortBy('due_date')->values(),
            'entries' => $entries,
        ];
    }

    // Reads the loan's journal entries and splits each into principal, interest, penalty and fees with a running balance.
    private function entries(Loan $loan): array
    {
        $roles = [
            'principal' => $this->ledger->accountId('loans_performing'),
            'interest' => $this->ledger->accountId('interest_income'),
            'penalty' => $this->ledger->accountId('penalty_income'),
            'fees' => $this->ledger->accountId('processing_fee_income'),
        ];

        $balance = 0.0;
        $rows = [];

        $journal = JournalEntry::where('reference_type', Loan::class)
            ->where('reference_id', $loan->id)
            ->whereIn('status', ['posted', 'reversed'])
            ->with('lines')
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        foreach ($journal as $entry) {
            $sum = fn (string $role, string $side) => round((float) $entry->lines
                ->where('account_id', $roles[$role])
                ->where('entry_type', $side)
                ->sum('amount'), 2);

            $disbursed = $sum('principal', 'debit');
            $principal = $sum('principal', 'credit');
            $balance = round($balance + $disbursed - $principal, 2);

            $rows[] = [
                'date' => $entry->entry_date->toDateString(),
                'entry_number' => $entry->entry_number,
                'description' => $entry->description,
                'type' => $entry->reversal_of_id ? 'reversal' : ($disbursed > 0 ? 'disbursement' : 'repayment'),
                'status' => $entry->status,
                'disbursed' => $disbursed,
                'principal' => $principal,
                'interest' => $sum('interest', 'credit'),
                'penalty' => $sum('penalty', 'credit'),
                'fees' => $sum('fees', 'credit'),
                'balance' => $balance,
            ];
        }

        return $rows;
    }

    // Collects the loan terms shown at the top of the statement.
    private function header(Loan $loan): array
    {
        return [
            'id' => $loan->id,
            'loan_number' => $loan->loan_number,
            'status' => $loan->status,
            'member_name' => $loan->member->first_name . ' ' . $loan->member->last_name,
            'membership_id' => $loan->member->membership_id,
            'product' => $loan->loanProduct->name ?? null,
            'disbursed_amount' => (float) $loan->disbursed_amount,
            'interest_rate' => (float) $loan->interest_rate,
            'term_months' => $loan->term_months,
            'disbursement_date' => $loan->disbursement_date?->toDateString(),
            'maturity_date' => $loan->maturity_date?->toDateString(),
        ];
    }

    // Totals the posted entries and adds the loan's current outstanding balance.
    private function summary(Loan $loan, array $entries): array
    {
        $live = collect($entries)->where('status', 'posted');

        return [
            'disbursed' => round($live->sum('disbursed'), 2),
            'principal_repaid' => round($live->sum('principal'), 2),
            'interest_paid' => round($live->sum('interest'), 2),
            'penalty_paid' => round($live->sum('penalty'), 2),
            'outstanding_balance' => (float) $loan->outstanding_balance,
        ];
    }
}