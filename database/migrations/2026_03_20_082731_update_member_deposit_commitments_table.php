<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_deposit_commitments', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id')->after('id');
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('effective_from')->nullable();

            $table->foreign('member_id')
                  ->references('id')
                  ->on('members')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('member_deposit_commitments', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->dropColumn(['member_id', 'amount', 'effective_from']);
        });
    }
};