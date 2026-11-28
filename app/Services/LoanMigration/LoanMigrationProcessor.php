<?php

namespace App\Services\LoanMigration;

use App\Enums\LoanMigrationBatchStatus;
use App\Enums\LoanMigrationProcessingStatus;
use App\Models\Loan;
use App\Models\LoanMigrationBatch;
use App\Models\LoanMigrationRecord;
use App\Models\User;
use App\Services\Accounting\AccountBalanceService;
use App\Services\Accounting\LedgerService;
use App\Services\Audit\AuditRecorder;
use App\Services\Loans\LoanAccountProvider;
use App\Services\Loans\LoanCalculatorService;
use App\Support\ErrorMessage;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class LoanMigrationProcessor
{
    public function __construct(
        private LoanCalculatorService $calculator,
        private LoanAccountProvider $loanAccounts,
        private AccountBalanceService $balances,
        private LedgerService $ledger,
        private AuditRecorder $audit,
    ) {}

    // Creates a loan for every unprocessed record of an approved batch and closes the batch when none are left.
    public function process(LoanMigrationBatch $batch, User $by): array
    {
        $this->assertApproved($batch);

        $created = 0;
        $failed = [];

        foreach ($this->pendingRecords($batch) as $record) {
            try {
                DB::transaction(fn() => $this->createLoan($record, $batch, $by));
                $created++;
            } catch (Throwable $e) {
                $record->markAsProcessingFailed(ErrorMessage::from($e));
                $record->save();
                $failed[] = $record->loan_number;
            }
        }

        return ['created' => $created, 'failed' => $failed, 'batch' => $this->closeIfFinished($batch, $by)];
    }

    // Throws unless the batch exists in the approved status.
    private function assertApproved(LoanMigrationBatch $batch): void
    {
        $status = LoanMigrationBatch::whereKey($batch->id)->first()?->status;

        if ($status !== LoanMigrationBatchStatus::APPROVED) {
            throw new DomainException('Only an approved batch can be processed.');
        }
    }

    // Returns the records still waiting for a loan, including ones that failed before.
    private function pendingRecords(LoanMigrationBatch $batch): Collection
    {
        return $batch->records()
            ->whereIn('processing_status', [LoanMigrationProcessingStatus::PENDING->value, LoanMigrationProcessingStatus::FAILED->value])
            ->orderBy('id')
            ->get();
    }

    // Builds the loan, its remaining schedule, the loan account balance and the opening ledger entry for one record.
    private function createLoan(LoanMigrationRecord $record, LoanMigrationBatch $batch, User $by): void
    {
        $record = LoanMigrationRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();

        if ($record->isProcessed()) {
            return;
        }

        $outstanding = round((float) $record->outstanding_balance, 2);
        $schedule = $outstanding > 0
            ? $this->calculator->schedule($outstanding, (float) $record->interest_rate, (int) $record->remaining_period, $record->next_due_date)
            : [];

        $loan = $this->storeLoan($record, $batch, $by, $outstanding, $schedule);

        $this->storeInstalments($loan, $schedule);
        $this->postOpeningBalance($loan, $record, $batch, $outstanding, $by);

        $record->markAsProcessed($loan->id);
        $record->save();

        $this->audit->record('loan_migration.loan_created', $loan, $by, [], [
            'batch' => $batch->batch_number,
            'record_id' => $record->id,
        ]);
    }

    // Creates the loan row from the legacy record, as active with a balance or completed without one.
    private function storeLoan(LoanMigrationRecord $record, LoanMigrationBatch $batch, User $by, float $outstanding, array $schedule): Loan
    {
        $interest = round(array_sum(array_column($schedule, 'interest_amount')), 2);
        $disbursed = Carbon::parse($record->date_disbursed);
        $maturity = $schedule
            ? end($schedule)['payment_date']
            : $disbursed->copy()->addMonthsNoOverflow((int) $record->repayment_period)->toDateString();

        return Loan::create([
            'loan_number' => $record->loan_number,
            'member_id' => $record->member_id,
            'loan_product_id' => $record->loan_product_id,
            'applied_amount' => $record->original_loan_amount,
            'approved_amount' => $record->original_loan_amount,
            'disbursed_amount' => $record->original_loan_amount,
            'interest_rate' => $record->interest_rate,
            'term_months' => $record->repayment_period,
            'monthly_repayment' => $schedule[0]['payment_amount'] ?? 0,
            'total_repayable' => round($outstanding + $interest, 2),
            'processing_fee' => 0,
            'insurance_fee' => 0,
            'purpose' => "Migrated from legacy system ({$batch->batch_number})",
            'status' => $outstanding > 0 ? 'active' : 'completed',
            'application_date' => $disbursed->toDateString(),
            'approval_date' => $disbursed->toDateString(),
            'disbursement_date' => $disbursed->toDateString(),
            'first_repayment_date' => $disbursed->copy()->addMonthNoOverflow()->toDateString(),
            'maturity_date' => $maturity,
            'approved_by' => $batch->approved_by,
            'disbursed_by' => $by->id,
            'approval_notes' => 'Migrated from legacy system',
            'outstanding_balance' => round($outstanding + $interest, 2),
            'principal_balance' => $outstanding,
            'interest_balance' => $interest,
            'penalty_balance' => 0,
            'days_in_arrears' => $schedule ? $this->daysLate($schedule[0]['payment_date']) : 0,
        ]);
    }

    // Saves the remaining schedule, marking instalments already past due as overdue.
    private function storeInstalments(Loan $loan, array $schedule): void
    {
        foreach ($schedule as $row) {
            $late = $this->daysLate($row['payment_date']);

            $loan->repayments()->create([
                'due_date' => $row['payment_date'],
                'expected_amount' => $row['payment_amount'],
                'principal_amount' => $row['principal_amount'],
                'interest_amount' => $row['interest_amount'],
                'penalty_amount' => 0,
                'paid_amount' => 0,
                'outstanding_amount' => $row['payment_amount'],
                'status' => $late > 0 ? 'overdue' : 'pending',
                'days_late' => $late,
            ]);
        }
    }

    // Puts the outstanding principal on the loan account and posts Dr loans against Cr opening balances.
    private function postOpeningBalance(Loan $loan, LoanMigrationRecord $record, LoanMigrationBatch $batch, float $outstanding, User $by): void
    {
        if ($outstanding <= 0) {
            return;
        }

        $description = "Migrated loan opening balance - {$loan->loan_number}";

        $this->balances->credit($this->loanAccounts->forMember($loan->member_id)->id, $outstanding, [
            'transaction_type' => 'loan_disbursement',
            'description' => $description,
            'reference_number' => $loan->loan_number,
            'payment_method' => 'system_transfer',
            'processed_by' => $by->id,
            'created_by' => $by->id,
            'idempotency_key' => "migration:{$record->id}:account",
            'metadata' => ['migration_batch' => $batch->batch_number, 'record_id' => $record->id],
        ]);

        $this->ledger->post([
            ['account' => 'loans_performing', 'type' => 'debit', 'amount' => $outstanding, 'description' => $description],
            ['account' => 'opening_balance', 'type' => 'credit', 'amount' => $outstanding, 'description' => $description],
        ], $description, $loan, "migration:{$record->id}:opening", $by->id);
    }

    // Counts how many days a due date is behind today, or zero when it is not yet due.
    private function daysLate(string $dueDate): int
    {
        return max(0, (int) Carbon::parse($dueDate)->diffInDays(today(), false));
    }

    // Marks the batch processed once every record has a loan, otherwise keeps it approved so it can be run again.
    private function closeIfFinished(LoanMigrationBatch $batch, User $by): LoanMigrationBatch
    {
        $batch = LoanMigrationBatch::whereKey($batch->id)->firstOrFail();
        $this->linkTopUps($batch);
        $processed = $batch->records()->where('processing_status', LoanMigrationProcessingStatus::PROCESSED->value)->count();

        $changes = ['processed_records' => $processed];

        if ($processed === (int) $batch->total_records) {
            $changes += ['status' => LoanMigrationBatchStatus::PROCESSED, 'processed_at' => now()];
        }

        $batch->update($changes);

        $this->audit->record('loan_migration.processed', $batch, $by, [], ['processed' => $processed, 'total' => $batch->total_records]);

        return $batch;
    }

    // Links each migrated top-up to its parent loan once every loan of the batch exists.
    private function linkTopUps(LoanMigrationBatch $batch): void
    {
        $records = $batch->records()->where('is_top_up', true)->whereNotNull('loan_id')->get();

        foreach ($records as $record) {
            $parentId = Loan::where('loan_number', trim((string) $record->parent_loan_number))->value('id');

            if ($parentId) {
                Loan::whereKey($record->loan_id)->update(['parent_loan_id' => $parentId, 'application_type' => 'top_up']);
            }
        }
    }
}
