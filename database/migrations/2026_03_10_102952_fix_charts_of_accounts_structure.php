<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        //  Add normal_balance column if it doesn't exist
        if (!Schema::hasColumn('charts_of_accounts', 'normal_balance')) {
            Schema::table('charts_of_accounts', function (Blueprint $table) {
                $table->enum('normal_balance', ['debit', 'credit'])
                      ->default('debit')
                      ->after('account_category');
            });
        }

        //  Widen account_type to include 'header' and 'contra_asset'
       
        DB::statement("
            ALTER TABLE `charts_of_accounts`
            MODIFY COLUMN `account_type`
            ENUM('asset','liability','equity','revenue','expense','header','contra_asset')
            NOT NULL DEFAULT 'asset'
        ");

        // 3. Clear the incorrectly seeded data so we can re-seed via the seeder
        DB::table('charts_of_accounts')->delete();
    }

    public function down(): void
    {
        // Restore the original enum (without header / contra_asset)
        DB::statement("
            ALTER TABLE `charts_of_accounts`
            MODIFY COLUMN `account_type`
            ENUM('asset','liability','equity','revenue','expense')
            NOT NULL DEFAULT 'asset'
        ");

        Schema::table('charts_of_accounts', function (Blueprint $table) {
            $table->dropColumn('normal_balance');
        });
    }
};