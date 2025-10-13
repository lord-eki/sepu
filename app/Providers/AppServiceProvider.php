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
        Gate::define('view-dashboard', function ($user) {
            return in_array($user->role, ['admin', 'management', 'accountant', 'loan_officer', 'member']);
        });

        Gate::define('view-dividends', function ($user) {
            return in_array($user->role, ['admin', 'management']);
        });

        Gate::define('view-budgets', function ($user) {
            return in_array($user->role, ['admin', 'management']);
        });

        Gate::define('view-vouchers', function ($user) {
            return in_array($user->role, ['admin', 'management', 'accountant']);
        });

        Gate::define('view-loans', function ($user) {
            return in_array($user->role, ['admin', 'management', 'loan_officer', 'member']);
        });
        
        Gate::define('view-members', function ($user) {
            return in_array($user->role, ['admin', 'management', 'loan_officer']);
        });


        Gate::define('view-accounts', function ($user) {
            return in_array($user->role, ['admin', 'management', 'accountant', 'member']);
        });
    }
}
