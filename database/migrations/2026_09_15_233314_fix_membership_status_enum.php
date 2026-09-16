<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE members MODIFY membership_status ENUM('pending', 'approved', 'active', 'inactive', 'suspended', 'rejected', 'terminated') NOT NULL DEFAULT 'pending'");
    }

};
