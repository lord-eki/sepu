<?php

namespace App\Services\LoanMigration;

use App\Enums\LoanMigrationProcessingStatus;
use App\Enums\LoanMigrationRecordValidationStatus;
use App\Models\LoanMigrationBatch;
use App\Models\LoanMigrationRecord;
use App\Models\Member;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use DomainException;
use Illuminate\Support\Facades\DB;

class LoanMigrationRecordService
{
    public function __construct(
        private LoanMigrationRecordValidator $validator,
        private LoanMigrationBatchTotals $totals,
        private AuditRecorder $audit,
    ) {}

    // Adds a manually entered record to an editable batch and validates it straight away.
    public function create(LoanMigrationBatch $batch, array $data, User $by): LoanMigrationRecord
    {
        return DB::transaction(function () use ($batch, $data, $by) {
            $batch = $this->editableBatch($batch);

            $record = (new LoanMigrationRecord())->forceFill(array_merge($this->attributes($data), [
                'batch_id' => $batch->id,
                'processing_status' => LoanMigrationProcessingStatus::PENDING,
            ]));
            $record->save();

            $this->check($record, false);
            $record->save();

            $this->totals->refresh($batch);
            $this->audit->record('loan_migration.record_added', $record, $by, [], ['loan_number' => $record->loan_number]);

            return $record;
        });
    }

    // Changes a record of an editable batch and validates it again, marking a fixed record as corrected.
    public function update(LoanMigrationBatch $batch, LoanMigrationRecord $record, array $data, User $by): LoanMigrationRecord
    {
        return DB::transaction(function () use ($batch, $record, $data, $by) {
            $batch = $this->editableBatch($batch);
            $record = $this->ownedRecord($batch, $record);

            $wasInvalid = $record->validation_status === LoanMigrationRecordValidationStatus::INVALID;
            $old = $record->only(['loan_number', 'original_loan_amount', 'outstanding_balance', 'loan_status']);

            $record->forceFill($this->attributes($data));
            $record->save();

            $this->check($record, $wasInvalid);
            $record->save();

            $this->totals->refresh($batch);
            $this->audit->record('loan_migration.record_updated', $record, $by, $old, $record->only(array_keys($old)));

            return $record;
        });
    }

    // Removes a record from an editable batch.
    public function delete(LoanMigrationBatch $batch, LoanMigrationRecord $record, User $by): void
    {
        DB::transaction(function () use ($batch, $record, $by) {
            $batch = $this->editableBatch($batch);
            $record = $this->ownedRecord($batch, $record);

            $this->audit->record('loan_migration.record_removed', $record, $by, ['loan_number' => $record->loan_number]);

            $record->delete();
            $this->totals->refresh($batch);
        });
    }

    // Locks the batch and throws unless it is still a draft or has failed validation.
    private function editableBatch(LoanMigrationBatch $batch): LoanMigrationBatch
    {
        $batch = LoanMigrationBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();

        if (! $batch->isEditable()) {
            throw new DomainException('Records can only be changed while the batch is a draft or has failed validation.');
        }

        return $batch;
    }

    // Reloads the record and throws when it does not belong to the batch.
    private function ownedRecord(LoanMigrationBatch $batch, LoanMigrationRecord $record): LoanMigrationRecord
    {
        $record = LoanMigrationRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();

        if ((int) $record->batch_id !== (int) $batch->id) {
            throw new DomainException('That record does not belong to this batch.');
        }

        return $record;
    }

    // Turns validated request data into record columns, filling the member number from the member.
    private function attributes(array $data): array
    {
        $member = Member::findOrFail($data['member_id']);
        $isTopUp = (bool) $data['is_top_up'];

        return [
            'member_id' => $member->id,
            'member_number' => $member->membership_id,
            'loan_product_id' => $data['loan_product_id'],
            'loan_number' => trim($data['loan_number']),
            'original_loan_amount' => $data['original_loan_amount'],
            'date_disbursed' => $data['date_disbursed'],
            'interest_rate' => $data['interest_rate'],
            'repayment_period' => $data['repayment_period'],
            'remaining_period' => $data['remaining_period'],
            'outstanding_balance' => $data['outstanding_balance'],
            'total_amount_paid' => $data['total_amount_paid'],
            'last_repayment_date' => $data['last_repayment_date'] ?? null,
            'next_due_date' => $data['next_due_date'] ?? null,
            'loan_status' => strtolower($data['loan_status']),
            'is_top_up' => $isTopUp,
            'parent_loan_number' => $isTopUp ? ($data['parent_loan_number'] ?? null) : null,
            'remarks' => $data['remarks'] ?? null,
        ];
    }

    // Validates the saved record and sets its status, using corrected when a previously invalid record now passes.
    private function check(LoanMigrationRecord $record, bool $wasInvalid): void
    {
        $errors = $this->validator->errors($record);

        if ($errors !== []) {
            $record->markAsInvalid($errors);

            return;
        }

        $record->markAsValid();

        if ($wasInvalid) {
            $record->validation_status = LoanMigrationRecordValidationStatus::CORRECTED;
        }
    }
}