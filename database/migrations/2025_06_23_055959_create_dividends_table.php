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
        Schema::create('dividends', function (Blueprint $table) {
            $table->id();
            $table->year('dividend_year');
            $table->decimal('total_profit', 15, 2);
            $table->decimal('dividend_rate', 5, 4); // Percentage rate
            $table->decimal('total_dividends', 15, 2);
            $table->enum('status', ['calculated', 'approved', 'distributed'])->default('calculated');
            $table->date('calculation_date');
            $table->date('approval_date')->nullable();
            $table->date('distribution_date')->nullable();
            $table->foreignId('calculated_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['dividend_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dividends');
    }
};
