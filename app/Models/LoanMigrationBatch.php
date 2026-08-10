<?php

namespace App\Models;

use App\Enums\LoanMigrationBatchStatus;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanMigrationBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'description',
        'status',

        'created_by',
        'submitted_by',
        'verified_by',
        'approved_by',

        'remarks',

        'total_records',
        'valid_records',
        'invalid_records',
        'processed_records',

        'total_original_amount',
        'total_amount_paid',
        'total_outstanding_balance',

        'submitted_at',
        'verified_at',
        'approved_at',
        'processed_at',
    ];

    protected $casts = [
    'status' => LoanMigrationBatchStatus::class,

    'total_original_amount' => 'decimal:2',
    'total_amount_paid' => 'decimal:2',
    'total_outstanding_balance' => 'decimal:2',

    'submitted_at' => 'datetime',
    'verified_at' => 'datetime',
    'approved_at' => 'datetime',
    'processed_at' => 'datetime',
];

    /**
     * User who created the migration batch.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who submitted the batch.
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Accounts Officer who verified the batch.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Administrator who approved the batch.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Migration records belonging to this batch.
     */
    public function records(): HasMany
    {
        return $this->hasMany(LoanMigrationRecord::class, 'batch_id');
    }

    /**
     * Check whether the batch is still editable.
     */
    public function isEditable(): bool
    {
        return $this->status?->isEditable() ?? false;
    }

    public function isSubmitted(): bool
    {
        return $this->status !== LoanMigrationBatchStatus::DRAFT
            && $this->status !== LoanMigrationBatchStatus::VALIDATION_FAILED;
    }

    public function isAccountsVerified(): bool
    {
        return $this->status?->isAccountsVerified() ?? false;
    }

    public function isApproved(): bool
    {
        return $this->status?->isApproved() ?? false;
    }

    public function isProcessed(): bool
    {
        return $this->status?->isProcessed() ?? false;
    }
    /**
     * Get the percentage of valid records.
     */
    public function getValidationPercentageAttribute(): float
    {
        if ($this->total_records === 0) {
            return 0;
        }

        return round(
            ($this->valid_records / $this->total_records) * 100,
            2
        );
    }

    /**
     * Get the percentage of processed records.
     */
    public function getProcessingPercentageAttribute(): float
    {
        if ($this->total_records === 0) {
            return 0;
        }

        return round(
            ($this->processed_records / $this->total_records) * 100,
            2
        );
    }
}