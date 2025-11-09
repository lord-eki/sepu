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

        Log::info('Login attempt', [
            'login' => $login,
            'ip' => $this->ip(),
        ]);

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$fieldType => $login, 'password' => $password];

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

        $user = Auth::user();
        $member = $user->member ?? null;

        Log::info('Login successful', [
            'user_id' => $user->id,
            'username' => $user->username ?? 'N/A',
            'role' => $user->role,
        ]);

        /**
         * Ensure profile completion first
         */
        if (!$member) {
            // Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Please complete your membership profile before logging in.',
            ]);
        }

        /**
         * Check membership status next
         */
        if ($member->membership_status === 'inactive') {
            // Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Your account is still awaiting activation by the admin.',
            ]);
        }

        /**
         * Now check if account is inactive or banned
         */
        if (!$user->is_active) {
            Auth::logout();

            Log::warning('Inactive or banned user attempted login', [
                'user_id' => $user->id,
            ]);

            throw ValidationException::withMessages([
                'login' => 'Your account is inactive. Please contact support.',
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