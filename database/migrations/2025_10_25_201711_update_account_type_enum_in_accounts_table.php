<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('new_account_type')->nullable()->after('account_type');
        });

        DB::table('accounts')->update([
            'new_account_type' => DB::raw("
                CASE 
                    WHEN account_type = 'savings' THEN 'share_deposits'
                    WHEN account_type = 'shares' THEN 'share_capital'
                    WHEN account_type = 'deposits' THEN 'share_deposits'
                    ELSE 'share_deposits'
                END
            ")
        ]);

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('account_type');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('account_type', ['share_capital', 'share_deposits'])
                ->default('share_deposits')
                ->after('member_id');
        });

        DB::table('accounts')->update([
            'account_type' => DB::raw('new_account_type')
        ]);

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('new_account_type');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('account_type', ['savings', 'shares', 'deposits'])
                ->default('savings')
                ->change();
        });
    }
};

