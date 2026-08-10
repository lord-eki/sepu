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
        Schema::create('loan_migration_records', function (Blueprint $table) {
            $table->id();

            /*
             * Migration batch this record belongs to.
             */
            $table->foreignId('batch_id')
                ->constrained('loan_migration_batches')
                ->cascadeOnDelete();

            /*
             * Existing SEPUSACCO member.
             *
             * We keep member_number as imported source information,
             * while member_id links to the actual member record.
             */
            $table->foreignId('member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();

            $table->string('member_number');

            /*
             * Existing loan information.
             */
            $table->string('loan_number');

            $table->foreignId('loan_product_id')
                ->nullable()
                ->constrained('loan_products')
                ->nullOnDelete();

            /*
             * Financial and loan-term information.
             */
            $table->decimal('original_loan_amount', 15, 2);

            $table->date('date_disbursed');

            $table->decimal('interest_rate', 8, 4);

            $table->unsignedInteger('repayment_period');

            $table->unsignedInteger('remaining_period');

            $table->decimal('outstanding_balance', 15, 2);

            $table->decimal('total_amount_paid', 15, 2);

            /*
             * Optional repayment dates.
             */
            $table->date('last_repayment_date')->nullable();

            $table->date('next_due_date')->nullable();

            /*
             * Existing loan status.
             *
             * Examples:
             * active
             * cleared
             * restructured
             */
            $table->string('loan_status');

            /*
             * Top-up information.
             */
            $table->boolean('is_top_up')->default(false);

            /*
             * Stored as the original/source loan number
             * during migration.
             *
             * We will resolve it to an actual migrated loan
             * when processing the batch.
             */
            $table->string('parent_loan_number')->nullable();

            /*
             * Additional information from the source records.
             */
            $table->text('remarks')->nullable();

            /*
             * Validation workflow.
             *
             * pending
             * valid
             * invalid
             * corrected
             */
            $table->string('validation_status')->default('pending');

            /*
             * Validation errors are stored as JSON so that
             * multiple problems can be displayed for one row.
             */
            $table->json('validation_errors')->nullable();

            /*
             * Processing workflow.
             *
             * pending
             * processing
             * processed
             * failed
             */
            $table->string('processing_status')->default('pending');

            /*
             * Error encountered while converting this
             * migration record into a real loan.
             */
            $table->text('processing_error')->nullable();

            /*
             * Once the migration is approved and processed,
             * this points to the actual record in loans.
             */
            $table->foreignId('loan_id')
                ->nullable()
                ->constrained('loans')
                ->nullOnDelete();

            /*
             * When this migration record was converted
             * into an actual loan.
             */
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            /*
             * Indexes.
             */
            $table->index('batch_id');

            $table->index('member_number');

            $table->index('loan_number');

            $table->index('validation_status');

            $table->index('processing_status');

            $table->index('is_top_up');

            /*
             * A loan number should not be duplicated
             * within the same migration batch.
             */
            $table->unique(
                ['batch_id', 'loan_number'],
                'migration_batch_loan_number_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_migration_records');
    }
};