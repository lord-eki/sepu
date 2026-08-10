<?php

namespace App\Enums;

enum LoanMigrationRecordValidationStatus: string
{
    case PENDING = 'pending';

    case VALID = 'valid';

    case INVALID = 'invalid';

    case CORRECTED = 'corrected';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::VALID => 'Valid',
            self::INVALID => 'Invalid',
            self::CORRECTED => 'Corrected',
        };
    }

    /**
     * Determine whether the record can proceed
     * to migration processing.
     */
    public function canBeProcessed(): bool
    {
        return in_array($this, [
            self::VALID,
            self::CORRECTED,
        ], true);
    }

    /**
     * Determine whether the record contains errors.
     */
    public function hasErrors(): bool
    {
        return $this === self::INVALID;
    }
}