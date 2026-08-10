<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temporarily remove the ENUM restriction
        DB::statement("
            ALTER TABLE loans
            MODIFY COLUMN status VARCHAR(255)
        ");

        // Add 'active' to the allowed loan statuses
        DB::statement("
            ALTER TABLE loans
            MODIFY COLUMN status ENUM(
                'pending_guarantor_approval',
                'pending',
                'approved',
                'rejected',
                'disbursed',
                'active',
                'completed',
                'defaulted',
                'written_off'
            ) NOT NULL DEFAULT 'pending_guarantor_approval'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change active loans back to disbursed
        DB::statement("
            ALTER TABLE loans
            MODIFY COLUMN status VARCHAR(255)
        ");

        DB::statement("
            UPDATE loans
            SET status = 'disbursed'
            WHERE status = 'active'
        ");

        // Remove 'active' from the ENUM
        DB::statement("
            ALTER TABLE loans
            MODIFY COLUMN status ENUM(
                'pending_guarantor_approval',
                'pending',
                'approved',
                'rejected',
                'disbursed',
                'completed',
                'defaulted',
                'written_off'
            ) NOT NULL DEFAULT 'pending_guarantor_approval'
        ");
    }
};