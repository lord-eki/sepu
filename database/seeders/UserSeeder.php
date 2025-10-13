<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Account;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@sepusacco.co.ke',
                'password' => Hash::make('admin123'),
                'phone' => '+254700000001',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'John Doe',
                'email' => 'manager@sepusacco.co.ke',
                'password' => Hash::make('manager123'),
                'phone' => '+254700000002',
                'role' => 'management',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'accountant@sepusacco.co.ke',
                'password' => Hash::make('accountant123'),
                'phone' => '+254700000003',
                'role' => 'accountant',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Peter Kamau',
                'email' => 'loanofficer@sepusacco.co.ke',
                'password' => Hash::make('officer123'),
                'phone' => '+254700000004',
                'role' => 'loan_officer',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
    
}
