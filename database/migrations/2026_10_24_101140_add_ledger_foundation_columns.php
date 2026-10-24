<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('transactions', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('transactions', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'idempotency_key')) {
                $table->string('idempotency_key', 100)->nullable()->unique();
            }
            if (!Schema::hasColumn('transactions', 'reversal_of_id')) {
                $table->foreignId('reversal_of_id')->nullable()->constrained('transactions')->nullOnDelete();
            }
            if (!Schema::hasColumn('transactions', 'journal_entry_id')) {
                $table->unsignedBigInteger('journal_entry_id')->nullable()->index();
            }
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entries', 'idempotency_key')) {
                $table->string('idempotency_key', 100)->nullable()->unique();
            }
            if (!Schema::hasColumn('journal_entries', 'reversal_of_id')) {
                $table->unsignedBigInteger('reversal_of_id')->nullable()->index();
            }
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
    }

};