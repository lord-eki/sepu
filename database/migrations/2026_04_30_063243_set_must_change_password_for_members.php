<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'member')
            ->update([
                'must_change_password' => true
            ]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'member')
            ->update([
                'must_change_password' => false
            ]);
    }
};