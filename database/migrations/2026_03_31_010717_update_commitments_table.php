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
        Schema::table('member_deposit_commitments', function (Blueprint $table) {

            // Type of setup
            $table->string('type')
                ->default('contribution')
                ->after('member_id');

            // Link to loan (for loan repayments)
            $table->foreignId('loan_id')
                ->nullable()
                ->after('account_id')
                ->constrained()
                ->nullOnDelete();

            // Dividend handling
            $table->string('dividend_mode')
                ->nullable()
                ->after('deduction_day'); // reinvest | payout

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_deposit_commitments', function (Blueprint $table) {

            $table->dropForeign(['loan_id']);
            $table->dropColumn([
                'type',
                'loan_id',
                'dividend_mode'
            ]);

        });
    }
};