<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loan_defaults', function (Blueprint $table) {
            $table->id();
            $table->decimal('interest_rate', 5, 2)->default(1.0);        // % per month
            $table->decimal('processing_fee_rate', 5, 2)->default(1.0); // % fee
            $table->decimal('insurance_rate', 5, 2)->default(0.5);      // % insurance
            $table->decimal('processing_fee_flat', 10, 2)->default(0);  // flat KSh fee
            $table->integer('min_amount')->default(1000);               // min loan
            $table->integer('max_amount')->default(100000);             // max loan
            $table->integer('min_term_months')->default(1);             // min months
            $table->integer('max_term_months')->default(12);            // max months
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_defaults');
    }
};