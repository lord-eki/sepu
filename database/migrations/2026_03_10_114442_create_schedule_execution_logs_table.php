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
        Schema::create('schedule_execution_logs', function (Blueprint $table) {
            $table->id();

            $table->enum('schedule_type', [
                'monthly_deposits',
                'loan_repayments',
                'loan_disbursements',
                'dividend_payments',
            ])->index();

            // Period the schedule was run for
            $table->tinyInteger('processing_month')->unsigned()->nullable()
                  ->comment('1–12; null for disbursements (not month-bound)');
            $table->smallInteger('processing_year')->unsigned();

            // Execution metadata
            $table->unsignedBigInteger('executed_by');
            $table->foreign('executed_by')->references('id')->on('users')->restrictOnDelete();

            $table->timestamp('execution_date')->useCurrent();

            $table->unsignedInteger('total_records_processed')->default(0);
            $table->unsignedInteger('total_records_failed')->default(0);
            $table->decimal('total_amount_posted', 15, 2)->default(0);

            $table->enum('status', ['completed', 'partial', 'failed'])->default('completed');

            // JSON log of any per-record errors that occurred
            $table->json('error_log')->nullable();

            $table->timestamps();

            // Prevent running the same schedule twice for the same period
            $table->unique(
                ['schedule_type', 'processing_month', 'processing_year'],
                'unique_schedule_period'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_execution_logs');
    }
};
