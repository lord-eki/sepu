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
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->enum('voucher_type', ['loan_disbursement', 'operational_expense', 'dividend_payment', 'refund', 'other']);
            $table->string('payee_name');
            $table->string('payee_phone')->nullable();
            $table->string('payee_account')->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('purpose');
            $table->text('description')->nullable();
            $table->foreignId('budget_item_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('loan_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'paid', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('paid_by')->nullable()->constrained('users');
            $table->date('approval_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('supporting_documents')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['voucher_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_vouchers');
    }
};
