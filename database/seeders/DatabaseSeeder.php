<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\BudgetSeeder;
use Database\Seeders\LoanProductSeeder;
use Database\Seeders\MemberSeeder;
use Database\Seeders\SystemSettingSeeder;
use Database\Seeders\UserSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

       $this->call([
        UserSeeder::class,
        SystemSettingSeeder::class,
        LoanProductSeeder::class,
        MemberSeeder::class,
        BudgetSeeder::class,
    ]);

    }
}
