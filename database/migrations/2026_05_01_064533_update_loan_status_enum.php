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
        // Step 0: remove ENUM restriction first
        DB::statement("
            ALTER TABLE loans MODIFY status VARCHAR(255)
        ");

        // Step 1: now cleanup works safely
        DB::statement("
            UPDATE loans 
            SET status = 'pending_guarantor_approval'
            WHERE status NOT IN (
                'pending_guarantor_approval',
                'pending',
                'approved',
                'rejected',
                'disbursed',
                'completed',
                'defaulted',
                'written_off'
            )
        ");

        // Step 2: restore ENUM safely
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