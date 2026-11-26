<?php

namespace App\Models;

use App\Enums\LoanMigrationRecordValidationStatus;
use App\Enums\LoanMigrationProcessingStatus;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanMigrationRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',

        'member_id',
        'member_number',

        'loan_number',
        'loan_product_id',

        'original_loan_amount',
        'date_disbursed',
        'interest_rate',
        'repayment_period',
        'remaining_period',

        'outstanding_balance',
        'total_amount_paid',

        'last_repayment_date',
        'next_due_date',

        'loan_status',

        'is_top_up',
        'parent_loan_number',

        'remarks',

        'validation_status',
        'validation_errors',

        'processing_status',
        'processing_error',

        'loan_id',
        'processed_at',
    ];

    protected $casts = [
        'validation_status' => LoanMigrationRecordValidationStatus::class,

        'processing_status' => LoanMigrationProcessingStatus::class,

        'original_loan_amount' => 'decimal:2',
        'interest_rate' => 'decimal:4',
        'outstanding_balance' => 'decimal:2',
        'total_amount_paid' => 'decimal:2',

        'date_disbursed' => 'date',
        'last_repayment_date' => 'date',
        'next_due_date' => 'date',

        'is_top_up' => 'boolean',

        'validation_errors' => 'array',

        'processed_at' => 'datetime',
    ];

  
    public function batch(): BelongsTo
    {
        return $this->belongsTo(
            LoanMigrationBatch::class,
            'batch_id'
        );
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(
            Member::class,
            'member_id'
        );
    }

   
    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(
            LoanProduct::class,
            'loan_product_id'
        );
    }

   
    public function loan(): BelongsTo
    {
        return $this->belongsTo(
            Loan::class,
            'loan_id'
        );
    }

   
    public function isValid(): bool
    {
        return $this->validation_status?->canBeProcessed() ?? false;
    }

    public function hasValidationErrors(): bool
    {
        return $this->validation_status?->hasErrors() ?? false;
    }

    public function isProcessed(): bool
    {
        return $this->processing_status?->isProcessed() ?? false;
    }

    public function isReadyForProcessing(): bool
    {
        return ($this->validation_status?->canBeProcessed() ?? false)
            && $this->processing_status === LoanMigrationProcessingStatus::PENDING;
    }

      
        public function addValidationError(
                string $field,
                string $message
            ): void {
                $errors = $this->validation_errors ?? [];

                $errors[$field][] = $message;

                $this->validation_errors = $errors;

                $this->validation_status =
                    LoanMigrationRecordValidationStatus::INVALID;
            }
   
    public function clearValidationErrors(): void
    {
        $this->validation_errors = null;
    }

    public function markAsValid(): void
    {
        $this->validation_status = LoanMigrationRecordValidationStatus::VALID;
        $this->validation_errors = null;
    }

    public function markAsInvalid(array $errors = []): void
    {
        $this->validation_status = LoanMigrationRecordValidationStatus::INVALID;
        $this->validation_errors = $errors;
    }

    public function markAsProcessed(int $loanId): void
    {
        $this->processing_status = LoanMigrationProcessingStatus::PROCESSED;
        $this->loan_id = $loanId;
        $this->processed_at = now();
        $this->processing_error = null;
    }

    public function markAsProcessingFailed(string $error): void
    {
        $this->processing_status = LoanMigrationProcessingStatus::FAILED;
        $this->processing_error = $error;
    }

    
}

