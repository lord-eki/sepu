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
         Schema::table('member_dividends', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('transaction_id');
        });

        
        DB::statement('ALTER TABLE dividends MODIFY notes JSON NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dividends', function (Blueprint $table) {
            //
        });
    }
};
