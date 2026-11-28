<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Adds the product top-up settings and the link from a top-up loan to the loan it tops up.
    public function up(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->boolean('allow_top_up')->default(false);
            $table->decimal('top_up_eligibility_percent', 5, 2)->default(50);
            $table->unsignedTinyInteger('max_top_ups')->default(1);
            $table->boolean('settle_existing_balance')->default(true);
            $table->decimal('max_top_up_amount', 15, 2)->nullable();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('parent_loan_id')->nullable()->constrained('loans')->nullOnDelete();
            $table->string('application_type', 20)->default('new');
            $table->decimal('top_up_settlement_amount', 15, 2)->default(0);
            $table->index('application_type');
        });
    }

    // Removes the top-up columns.
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_loan_id');
            $table->dropIndex(['application_type']);
            $table->dropColumn(['application_type', 'top_up_settlement_amount']);
        });

        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn(['allow_top_up', 'top_up_eligibility_percent', 'max_top_ups', 'settle_existing_balance', 'max_top_up_amount']);
        });
    }
};