<?php

namespace App\Enums;

enum LoanMigrationProcessingStatus: string
{
    case PENDING = 'pending';

    case PROCESSING = 'processing';

    case PROCESSED = 'processed';

    case FAILED = 'failed';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::PROCESSED => 'Processed',
            self::FAILED => 'Failed',
        };
    }

    /**
     * Determine whether the record has
     * successfully become a real loan.
     */
    public function isProcessed(): bool
    {
        return $this === self::PROCESSED;
    }

    /**
     * Determine whether processing failed.
     */
    public function hasFailed(): bool
    {
        return $this === self::FAILED;
    }
}