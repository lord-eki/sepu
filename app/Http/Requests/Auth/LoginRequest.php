<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

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
        $user = User::where($fieldType, $login)->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'login' => 'No account found with these credentials.',
            ]);
        }

        /**
         *  Membership status checks (before login)
         */
        if ($user->role === 'member') {
            $member = $user->member;

            if (!$member) {
                throw ValidationException::withMessages([
                    'login' => 'Please complete your membership profile before logging in.',
                ]);
            }

            switch ($member->membership_status) {
                case 'pending':
                    throw ValidationException::withMessages([
                        'login' => 'Your membership is pending approval by the admin.',
                    ]);

                case 'inactive':
                    throw ValidationException::withMessages([
                        'login' => 'Your membership is inactive. Please contact support.',
                    ]);

                case 'suspended':
                    throw ValidationException::withMessages([
                        'login' => 'Your membership has been suspended. Please contact support for more information.',
                    ]);

                case 'rejected':
                    throw ValidationException::withMessages([
                        'login' => 'Your membership application was rejected. You cannot log in.',
                    ]);

                case 'terminated':
                    throw ValidationException::withMessages([
                        'login' => 'Your membership has been terminated. Access is no longer allowed.',
                    ]);
            }
        }

        /**
         *  Account-level check
         */
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'login' => 'Your account has been deactivated. Please contact support.',
            ]);
        }

        /**
         *  Finally attempt authentication
         */
        if (!Auth::attempt([$fieldType => $login, 'password' => $password], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            Log::warning('Login failed', [
                'login' => $login,
                'field_type' => $fieldType,
            ]);

            throw ValidationException::withMessages([
                'login' => 'Invalid credentials. Please try again.',
            ]);
        }

        /**
         *  Login successful
         */
        $user = Auth::user();

        Log::info('Login successful', [
            'user_id' => $user->id,
            'username' => $user->username ?? 'N/A',
            'role' => $user->role,
        ]);

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
