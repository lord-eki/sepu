<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->foreignId('payee_id')->nullable()->constrained('members');
        });
    }

    public function down(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropForeign(['payee_id']);
            $table->dropColumn('payee_id');
        });
    }
};

