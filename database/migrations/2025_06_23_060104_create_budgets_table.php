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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->year('budget_year');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('total_budget', 15, 2);
            $table->enum('status', ['draft', 'approved', 'active', 'closed'])->default('draft');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->date('approval_date')->nullable();
            $table->timestamps();
            
            $table->unique(['budget_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
