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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE loans 
            MODIFY COLUMN status ENUM(
                'pending',
                'approved',
                'rejected',
                'disbursed',
                'completed',
                'defaulted',
                'written_off'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};