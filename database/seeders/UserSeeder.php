<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('sepu.admin_email');

        if (User::where('email', $email)->exists()) {
            $this->command?->info("Admin {$email} already exists. Nothing changed.");
            return;
        }

        $password = config('sepu.admin_password') ?: Str::password(16);

        (new User())->forceFill([
            'name'                 => 'System Administrator',
            'email'                => $email,
            'password'             => Hash::make($password),
            'phone'                => '+254700000001',
            'role'                 => 'admin',
            'is_active'            => true,
            'email_verified_at'    => now(),
            'must_change_password' => true, 
        ])->save();

        if (!config('sepu.admin_password')) {
            $this->command?->warn("Admin created: {$email}  password: {$password}");
            $this->command?->warn('This is shown once. Log in and change it immediately.');
        }
    }
}