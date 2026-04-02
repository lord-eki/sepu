<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dividend_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();   // e.g. share_dividend_rate
            $table->decimal('value', 15, 4);   // e.g. 17.0000
            $table->string('label')->nullable(); // Human-readable label
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed the default rates that DividendController already expects
        DB::table('dividend_settings')->insert([
            [
                'key'         => 'share_dividend_rate',
                'value'       => 17.00,
                'label'       => 'Share Dividend Rate (%)',
                'description' => 'Annual dividend rate applied to share capital balance as at 31 Dec.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'deposit_interest_rate',
                'value'       => 11.00,
                'label'       => 'Deposit Interest Rate (%)',
                'description' => 'Annual interest rate applied to monthly qualifying deposits.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'tax_rate',
                'value'       => 5.00,
                'label'       => 'Tax Rate (%)',
                'description' => 'Withholding tax rate applied on gross dividend.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'processing_fee',
                'value'       => 300.00,
                'label'       => 'Processing Fee (KES)',
                'description' => 'Fixed processing fee deducted from each member\'s dividend.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'exercise_duty',
                'value'       => 60.00,
                'label'       => 'Excise Duty (KES)',
                'description' => 'Fixed excise duty deducted from each member\'s dividend.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dividend_settings');
    }
};