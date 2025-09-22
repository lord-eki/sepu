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
        Schema::create('loan_guarantors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('guarantor_member_id')->constrained('members')->onDelete('cascade');
            $table->decimal('guaranteed_amount', 15, 2);
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamp('response_date')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
            
            $table->unique(['loan_id', 'guarantor_member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_guarantors');
    }
};
