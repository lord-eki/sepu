<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
    {
        Schema::create('member_finance_configs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                  ->unique()
                  ->constrained('members')
                  ->cascadeOnDelete();

            // ── 1. Monthly Deposit ────────────────────────────────────────
            $table->boolean('contribution_active')->default(false)
                  ->comment('Include member in the monthly deposit schedule run');

            $table->decimal('monthly_contribution', 15, 2)->default(0)
                  ->comment('Amount to credit to the member\'s account each month');

            $table->foreignId('contribution_account_id')->nullable()
                  ->constrained('accounts')
                  ->nullOnDelete()
                  ->comment('Account to credit during monthly deposit run');

            // ── 2. Loan Repayment ─────────────────────────────────────────
            $table->boolean('loan_auto_deduct')->default(false)
                  ->comment('Include member\'s active loans in the loan repayment schedule');

            $table->decimal('loan_deduction_amount', 15, 2)->nullable()
                  ->comment('Fixed monthly deduction amount for loan repayment (null = use instalment from loan_repayments table)');

            // ── 3. Dividend ───────────────────────────────────────────────
            $table->boolean('dividend_eligible')->default(true)
                  ->comment('Include member in the annual dividend payment run');

            $table->foreignId('dividend_account_id')->nullable()
                  ->constrained('accounts')
                  ->nullOnDelete()
                  ->comment('Account to credit dividend payment (falls back to FOSA if null)');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_finance_configs');
    }
};