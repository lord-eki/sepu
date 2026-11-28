<?php

namespace App\Services\LoanMigration;

use App\Enums\LoanMigrationRecordValidationStatus;
use App\Models\LoanMigrationBatch;

class LoanMigrationBatchTotals
{
    // Recounts the batch's records and amounts and stores them on the batch.
    public function refresh(LoanMigrationBatch $batch): LoanMigrationBatch
    {
        $sum = $batch->records()
            ->selectRaw('count(*) as total, coalesce(sum(original_loan_amount), 0) as original, coalesce(sum(total_amount_paid), 0) as paid, coalesce(sum(outstanding_balance), 0) as outstanding')
            ->first();

        $batch->update([
            'total_records' => (int) $sum->total,
            'valid_records' => $batch->records()->whereIn('validation_status', [
                LoanMigrationRecordValidationStatus::VALID->value,
                LoanMigrationRecordValidationStatus::CORRECTED->value,
            ])->count(),
            'invalid_records' => $batch->records()
                ->where('validation_status', LoanMigrationRecordValidationStatus::INVALID->value)
                ->count(),
            'total_original_amount' => round((float) $sum->original, 2),
            'total_amount_paid' => round((float) $sum->paid, 2),
            'total_outstanding_balance' => round((float) $sum->outstanding, 2),
        ]);

        return $batch;
    }
}