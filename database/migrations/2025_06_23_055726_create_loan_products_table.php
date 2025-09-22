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
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('min_amount', 15, 2);
            $table->decimal('max_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2); // Annual interest rate
            $table->integer('min_term_months');
            $table->integer('max_term_months');
            $table->decimal('processing_fee_rate', 5, 2)->default(0.00);
            $table->decimal('insurance_rate', 5, 2)->default(0.00);
            $table->integer('grace_period_days')->default(0);
            $table->decimal('penalty_rate', 5, 2)->default(0.00);
            $table->json('eligibility_criteria')->nullable();
            $table->json('required_documents')->nullable();
            $table->boolean('requires_guarantor')->default(false);
            $table->integer('min_guarantors')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
