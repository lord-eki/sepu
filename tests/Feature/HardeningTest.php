<?php

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;

it('has no default admin password in the configuration file', function () {
    expect(file_get_contents(config_path('sepu.php')))->not->toContain('admin123');
})->group('security');

it('leaves the admin password alone when the seeder runs again', function () {
    $this->seed(UserSeeder::class);
    $first = User::where('email', config('sepu.admin_email'))->value('password');

    $this->seed(UserSeeder::class);
    $second = User::where('email', config('sepu.admin_email'))->value('password');

    expect($second)->toBe($first);
})->group('security');

it('forces the seeded admin to change the password', function () {
    $this->seed(UserSeeder::class);

    $admin = User::where('email', config('sepu.admin_email'))->first();

    expect($admin->role)->toBe('admin')->and((bool) $admin->must_change_password)->toBeTrue();
})->group('security');

it('only returns raw exception messages from domain exception catches', function () {
    $offenders = [];

    foreach (File::allFiles(app_path('Http/Controllers')) as $file) {
        $lines = explode("\n", $file->getContents());

        foreach ($lines as $index => $line) {
            if (! str_contains($line, '->getMessage()') || str_contains($line, 'ErrorMessage')) {
                continue;
            }

            $catch = '';

            for ($i = $index; $i >= 0; $i--) {
                if (str_contains($lines[$i], 'catch (')) {
                    $catch = $lines[$i];
                    break;
                }
            }

            if (! str_contains($catch, 'DomainException')) {
                $offenders[] = $file->getRelativePathname() . ':' . ($index + 1);
            }
        }
    }

    expect($offenders)->toBe([]);
})->group('security');

it('registers the money rate limiter', function () {
    expect(RateLimiter::limiter('money'))->not->toBeNull();
})->group('security');

it('creates self registered users as inactive members whatever the form says', function () {
    $this->post('/register', [
        'name' => 'New Person',
        'email' => 'new.person@example.test',
        'country_code' => '+254',
        'phone' => '0712345678',
        'password' => 'Str0ng!Passw0rd#26',
        'password_confirmation' => 'Str0ng!Passw0rd#26',
        'role' => 'admin',
        'is_active' => true,
    ]);

    $user = User::where('email', 'new.person@example.test')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('member')
        ->and((bool) $user->is_active)->toBeFalse();
})->group('security');