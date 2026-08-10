<?php

namespace App\Enums;

enum LoanMigrationBatchStatus: string
{
    case DRAFT = 'draft';

    case VALIDATING = 'validating';

    case VALIDATION_FAILED = 'validation_failed';

    case VALIDATED = 'validated';

    case SUBMITTED = 'submitted';

    case ACCOUNTS_VERIFIED = 'accounts_verified';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';

    case PROCESSED = 'processed';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::VALIDATING => 'Validating',
            self::VALIDATION_FAILED => 'Validation Failed',
            self::VALIDATED => 'Validated',
            self::SUBMITTED => 'Submitted',
            self::ACCOUNTS_VERIFIED => 'Accounts Verified',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::PROCESSED => 'Processed',
        };
    }

    /**
     * Determine whether the batch can still be edited.
     */
    public function isEditable(): bool
    {
        return in_array($this, [
            self::DRAFT,
            self::VALIDATION_FAILED,
        ], true);
    }

    /**
     * Determine whether the batch has passed
     * Accounts Officer verification.
     */
    public function isAccountsVerified(): bool
    {
        return in_array($this, [
            self::ACCOUNTS_VERIFIED,
            self::APPROVED,
            self::PROCESSED,
        ], true);
    }

    /**
     * Determine whether the batch has been approved.
     */
    public function isApproved(): bool
    {
        return in_array($this, [
            self::APPROVED,
            self::PROCESSED,
        ], true);
    }

    /**
     * Determine whether the batch has been processed.
     */
    public function isProcessed(): bool
    {
        return $this === self::PROCESSED;
    }
}