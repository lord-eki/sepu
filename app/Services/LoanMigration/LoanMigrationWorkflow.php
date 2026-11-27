<?php

namespace App\Services\LoanMigration;

use App\Enums\LoanMigrationBatchStatus;
use App\Enums\LoanMigrationRecordValidationStatus;
use App\Models\LoanMigrationBatch;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use DomainException;
use Illuminate\Support\Facades\DB;

class LoanMigrationWorkflow
{
    public function __construct(
        private LoanMigrationRecordValidator $validator,
        private AuditRecorder $audit,
    ) {}

    // Re-checks every record and moves the batch to validated or validation failed.
    public function validate(LoanMigrationBatch $batch, User $by): LoanMigrationBatch
    {
        return DB::transaction(function () use ($batch, $by) {
            $batch = $this->lock($batch);

            $this->assertStatus($batch, [
                LoanMigrationBatchStatus::DRAFT,
                LoanMigrationBatchStatus::VALIDATION_FAILED,
                LoanMigrationBatchStatus::VALIDATED,
            ], 'validated');

            if (! $batch->records()->exists()) {
                throw new DomainException('The batch has no records to validate.');
            }

            $invalid = $this->revalidateRecords($batch);
            $totals = $this->recordTotals($batch);

            $batch->update(array_merge($totals, [
                'valid_records' => $totals['total_records'] - $invalid,
                'invalid_records' => $invalid,
                'status' => $invalid === 0 ? LoanMigrationBatchStatus::VALIDATED : LoanMigrationBatchStatus::VALIDATION_FAILED,
            ]));

            $this->audit->record('loan_migration.validated', $batch, $by, [], [
                'valid' => $batch->valid_records,
                'invalid' => $batch->invalid_records,
            ]);

            return $batch;
        });
    }

    // Submits a fully valid batch for Accounts Officer verification.
    public function submit(LoanMigrationBatch $batch, User $by, ?string $remarks = null): LoanMigrationBatch
    {
        return $this->step($batch, $by, [LoanMigrationBatchStatus::VALIDATED], 'submitted', 'loan_migration.submitted', [
            'status' => LoanMigrationBatchStatus::SUBMITTED,
            'submitted_by' => $by->id,
            'submitted_at' => now(),
            'remarks' => $remarks,
        ]);
    }

    // Records the Accounts Officer's confirmation that the batch totals match its records.
    public function verifyAccounts(LoanMigrationBatch $batch, User $by, ?string $remarks = null): LoanMigrationBatch
    {
        return DB::transaction(function () use ($batch, $by, $remarks) {
            $batch = $this->lock($batch);

            $this->assertStatus($batch, [LoanMigrationBatchStatus::SUBMITTED], 'verified');
            $this->assertTotalsMatch($batch);

            $batch->update([
                'status' => LoanMigrationBatchStatus::ACCOUNTS_VERIFIED,
                'verified_by' => $by->id,
                'verified_at' => now(),
                'remarks' => $remarks ?? $batch->remarks,
            ]);

            $this->audit->record('loan_migration.accounts_verified', $batch, $by);

            return $batch;
        });
    }

    // Approves a batch that the Accounts Officer has verified.
    public function approve(LoanMigrationBatch $batch, User $by, ?string $remarks = null): LoanMigrationBatch
    {
        return $this->step($batch, $by, [LoanMigrationBatchStatus::ACCOUNTS_VERIFIED], 'approved', 'loan_migration.approved', [
            'status' => LoanMigrationBatchStatus::APPROVED,
            'approved_by' => $by->id,
            'approved_at' => now(),
            'remarks' => $remarks ?? $batch->remarks,
        ]);
    }

    // Turns a submitted or verified batch down and records the reason.
    public function reject(LoanMigrationBatch $batch, User $by, string $reason): LoanMigrationBatch
    {
        return $this->step(
            $batch,
            $by,
            [LoanMigrationBatchStatus::SUBMITTED, LoanMigrationBatchStatus::ACCOUNTS_VERIFIED],
            'rejected',
            'loan_migration.rejected',
            ['status' => LoanMigrationBatchStatus::REJECTED, 'remarks' => $reason],
        );
    }

    // Locks the batch, checks its status, applies the changes and writes the audit row.
    private function step(LoanMigrationBatch $batch, User $by, array $allowed, string $verb, string $action, array $changes): LoanMigrationBatch
    {
        return DB::transaction(function () use ($batch, $by, $allowed, $verb, $action, $changes) {
            $batch = $this->lock($batch);

            $this->assertStatus($batch, $allowed, $verb);

            $from = $batch->status->value;
            $batch->update($changes);

            $this->audit->record($action, $batch, $by, ['status' => $from], ['status' => $batch->status->value]);

            return $batch;
        });
    }

    // Reloads the batch with a row lock so two people cannot move it at once.
    private function lock(LoanMigrationBatch $batch): LoanMigrationBatch
    {
        return LoanMigrationBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
    }

    // Throws unless the batch is in one of the allowed statuses.
    private function assertStatus(LoanMigrationBatch $batch, array $allowed, string $verb): void
    {
        if (! in_array($batch->status, $allowed, true)) {
            throw new DomainException("A {$batch->status->label()} batch cannot be {$verb}.");
        }
    }

    // Validates each record, saves its result and returns how many are invalid.
    private function revalidateRecords(LoanMigrationBatch $batch): int
    {
        $invalid = 0;

        foreach ($batch->records()->orderBy('id')->get() as $record) {
            $errors = $this->validator->errors($record);

            if ($errors === []) {
                $record->markAsValid();
            } else {
                $record->markAsInvalid($errors);
                $invalid++;
            }

            $record->save();
        }

        return $invalid;
    }

    // Sums the batch's records into the totals stored on the batch.
    private function recordTotals(LoanMigrationBatch $batch): array
    {
        $sum = $batch->records()
            ->selectRaw('count(*) as total, coalesce(sum(original_loan_amount), 0) as original, coalesce(sum(total_amount_paid), 0) as paid, coalesce(sum(outstanding_balance), 0) as outstanding')
            ->first();

        return [
            'total_records' => (int) $sum->total,
            'total_original_amount' => round((float) $sum->original, 2),
            'total_amount_paid' => round((float) $sum->paid, 2),
            'total_outstanding_balance' => round((float) $sum->outstanding, 2),
        ];
    }

    // Throws when the stored batch totals no longer match the sum of its records or any record is not valid.
    private function assertTotalsMatch(LoanMigrationBatch $batch): void
    {
        $totals = $this->recordTotals($batch);

        foreach (['total_original_amount', 'total_amount_paid', 'total_outstanding_balance'] as $field) {
            if (abs((float) $batch->{$field} - $totals[$field]) > 0.005) {
                throw new DomainException('The batch totals do not match its records. Validate the batch again before verifying.');
            }
        }

        $notValid = $batch->records()
            ->whereNotIn('validation_status', [LoanMigrationRecordValidationStatus::VALID->value, LoanMigrationRecordValidationStatus::CORRECTED->value])
            ->exists();

        if ($notValid) {
            throw new DomainException('Every record must be valid before the accounts can be verified.');
        }
    }
}