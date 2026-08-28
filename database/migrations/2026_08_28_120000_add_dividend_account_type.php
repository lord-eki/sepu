<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE accounts MODIFY account_type ENUM('share_capital', 'share_deposits', 'loan_outstanding', 'dividend') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE accounts MODIFY account_type ENUM('share_capital', 'share_deposits', 'loan_outstanding') NOT NULL");
    }
};