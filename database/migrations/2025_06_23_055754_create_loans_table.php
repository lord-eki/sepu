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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number')->unique();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_product_id')->constrained()->onDelete('cascade');
            $table->decimal('applied_amount', 15, 2);
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->decimal('disbursed_amount', 15, 2)->nullable();
            $table->decimal('interest_rate', 5, 2);
            $table->integer('term_months');
            $table->decimal('monthly_repayment', 15, 2)->nullable();
            $table->decimal('total_repayable', 15, 2)->nullable();
            $table->decimal('processing_fee', 15, 2)->default(0.00);
            $table->decimal('insurance_fee', 15, 2)->default(0.00);
            $table->text('purpose');
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'disbursed', 'active', 'completed', 'defaulted', 'written_off'])->default('pending');
            $table->date('application_date');
            $table->date('approval_date')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->date('first_repayment_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('disbursed_by')->nullable()->constrained('users');
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('documents')->nullable();
            $table->decimal('outstanding_balance', 15, 2)->default(0.00);
            $table->decimal('principal_balance', 15, 2)->default(0.00);
            $table->decimal('interest_balance', 15, 2)->default(0.00);
            $table->decimal('penalty_balance', 15, 2)->default(0.00);
            $table->integer('days_in_arrears')->default(0);
            $table->timestamps();
            
            $table->index(['member_id', 'status']);
            $table->index(['status']);
            $table->index(['application_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
