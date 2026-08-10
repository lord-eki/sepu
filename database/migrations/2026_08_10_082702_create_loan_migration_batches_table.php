<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loan_migration_batches', function (Blueprint $table) {
            $table->id();

            /*
             * Unique migration batch reference.
             * Example: MIG-2026-001
             */
            $table->string('batch_number')->unique();

            /*
             * Optional description of the migration exercise.
             */
            $table->string('description')->nullable();

            /*
             * Batch workflow:
             *
             * draft
             * validating
             * validation_failed
             * validated
             * submitted
             * accounts_verified
             * approved
             * rejected
             * processed
             */
            $table->string('status')->default('draft');

            /*
             * User who created the batch.
             */
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            /*
             * User who submitted the batch for verification.
             */
            $table->foreignId('submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Accounts Officer who verified
             * the financial information.
             */
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Administrator who approved
             * the migration.
             */
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Rejection or general workflow remarks.
             */
            $table->text('remarks')->nullable();

            /*
             * Summary information.
             *
             * These are useful for displaying
             * batch statistics without repeatedly
             * calculating them.
             */
            $table->unsignedInteger('total_records')->default(0);

            $table->unsignedInteger('valid_records')->default(0);

            $table->unsignedInteger('invalid_records')->default(0);

            $table->unsignedInteger('processed_records')->default(0);

            /*
             * Financial summary.
             */
            $table->decimal('total_original_amount', 15, 2)->default(0);

            $table->decimal('total_amount_paid', 15, 2)->default(0);

            $table->decimal('total_outstanding_balance', 15, 2)->default(0);

            /*
             * Workflow timestamps.
             */
            $table->timestamp('submitted_at')->nullable();

            $table->timestamp('verified_at')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            /*
             * Useful indexes for filtering batches.
             */
            $table->index('status');

            $table->index('created_by');

            $table->index('submitted_by');

            $table->index('verified_by');

            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_migration_batches');
    }
};