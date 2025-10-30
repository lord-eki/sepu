<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define Gates based on user roles
        Gate::define('view-dashboard', function ($user) {
            return in_array($user->role, ['admin', 'management', 'accountant', 'loan_officer', 'member']);
        });

        Gate::define('view-dividends', function ($user) {
            return in_array($user->role, ['admin', 'management', 'accountant', 'member']);
        });

        Gate::define('view-budgets', function ($user) {
            return in_array($user->role, ['admin', 'management', 'accountant']);
        });

        Gate::define('view-vouchers', function ($user) {
            return in_array($user->role, ['admin', 'management', 'accountant']);
        });

        Gate::define('view-loans', function ($user) {
            return in_array($user->role, ['admin', 'management', 'accountant', 'loan_officer', 'member']);
        });

        Gate::define('view-members', function ($user) {
            return in_array($user->role, ['admin', 'management', 'accountant', 'loan_officer']);
        });

        Gate::define('view-accounts', function ($user) {
            return in_array($user->role, ['admin', 'management', 'accountant', 'member']);
        });

        // Admin gates
        Gate::define('manage-users', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('manage-system-settings', function ($user) {
            return $user->role === 'admin';
        });

        // Management gates
        Gate::define('approve-loans', function ($user) {
            return in_array($user->role, ['admin', 'management']);
        });

        Gate::define('approve-budgets', function ($user) {
            return in_array($user->role, ['admin', 'management']);
        });

        Gate::define('approve-vouchers', function ($user) {
            return in_array($user->role, ['admin', 'management']);
        });

        // Accountant gates
        Gate::define('process-transactions', function ($user) {
            return in_array($user->role, ['admin', 'accountant']);
        });

        Gate::define('generate-reports', function ($user) {
            return in_array($user->role, ['admin', 'management', 'accountant']);
        });

        // Loan Officer gates
        Gate::define('process-loan-applications', function ($user) {
            return in_array($user->role, ['admin', 'loan_officer']);
        });
    }
}
