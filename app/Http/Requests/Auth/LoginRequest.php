<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'Please enter your username or email.',
            'password.required' => 'Please enter your password.',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('login');
        $password = $this->input('password');

        // DEBUG: Log the login attempt
        Log::info('Login attempt', [
            'login' => $login,
            'ip' => $this->ip(),
        ]);

        // Determine if login is email or username
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // DEBUG: Log the field type
        Log::info('Login field type determined', [
            'field_type' => $fieldType,
            'login_value' => $login,
        ]);

        // Attempt authentication
        $credentials = [$fieldType => $login, 'password' => $password];
        
        Log::info('Attempting authentication with', [
            'credentials_keys' => array_keys($credentials),
            $fieldType => $login,
        ]);

        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            Log::warning('Login failed', [
                'login' => $login,
                'field_type' => $fieldType,
            ]);

            throw ValidationException::withMessages([
                'login' => 'These credentials do not match our records.',
            ]);
        }

        // Check if user is active
        $user = Auth::user();
        
        Log::info('Login successful', [
            'user_id' => $user->id,
            'username' => $user->username ?? 'N/A',
            'role' => $user->role,
        ]);

        if (!$user->is_active) {
            Auth::logout();
            
            Log::warning('Inactive user attempted login', [
                'user_id' => $user->id,
            ]);

            throw ValidationException::withMessages([
                'login' => 'Your account has been deactivated. Please contact support.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')) . '|' . $this->ip());
    }
}