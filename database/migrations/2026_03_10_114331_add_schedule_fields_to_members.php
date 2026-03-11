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
        Schema::table('members', function (Blueprint $table) {
            $table->decimal('monthly_contribution_amount', 15, 2)
                  ->nullable()
                  ->default(0)
                  ->after('monthly_income')
                  ->comment('Fixed monthly deposit amount used by the deposit schedule');

            $table->boolean('dividend_eligibility')
                  ->default(true)
                  ->after('monthly_contribution_amount')
                  ->comment('Whether this member qualifies for dividend payments');

            $table->unsignedBigInteger('dividend_account_id')
                  ->nullable()
                  ->after('dividend_eligibility')
                  ->comment('Account where dividend will be credited; defaults to share_deposits account');

            $table->foreign('dividend_account_id')
                  ->references('id')
                  ->on('accounts')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            //
        });
    }
};
