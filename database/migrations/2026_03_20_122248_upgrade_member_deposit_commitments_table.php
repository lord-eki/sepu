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

            // Rename existing 'amount' to 'monthly_amount'
            if (Schema::hasColumn('member_deposit_commitments', 'amount')) {
                $table->renameColumn('amount', 'monthly_amount');
            }

            // Add missing columns required by controller
            if (!Schema::hasColumn('member_deposit_commitments', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->after('member_id');
            }

            if (!Schema::hasColumn('member_deposit_commitments', 'account_type')) {
                $table->string('account_type')->after('account_id');
            }

            if (!Schema::hasColumn('member_deposit_commitments', 'deduction_day')) {
                $table->integer('deduction_day')->default(1);
            }

            if (!Schema::hasColumn('member_deposit_commitments', 'effective_to')) {
                $table->date('effective_to')->nullable();
            }

            if (!Schema::hasColumn('member_deposit_commitments', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            if (!Schema::hasColumn('member_deposit_commitments', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_deposit_commitments', function (Blueprint $table) {

            // Rename back monthly_amount → amount
            if (Schema::hasColumn('member_deposit_commitments', 'monthly_amount')) {
                $table->renameColumn('monthly_amount', 'amount');
            }

            // Drop the added columns
            $table->dropColumn([
                'account_id',
                'account_type',
                'deduction_day',
                'effective_to',
                'is_active',
                'notes',
            ]);
        });
    }
};