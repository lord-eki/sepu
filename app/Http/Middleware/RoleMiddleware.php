<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user is active
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['account' => 'Your account has been deactivated. Please contact the administrator.']);
        }

        // If no specific roles are required, allow any authenticated active user
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            // Redirect to appropriate dashboard based on user role
            $dashboardRoute = $this->getDashboardRouteForRole($user->role);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access denied. Insufficient permissions.',
                    'redirect' => route($dashboardRoute)
                ], 403);
            }

            return redirect()->route($dashboardRoute)
                ->with('error', 'Access denied. You do not have permission to access this resource.');
        }

        return $next($request);
    }

    /**
     * Get the appropriate dashboard route for a user role
     */
    private function getDashboardRouteForRole(string $role): string
    {
        return match($role) {
            'admin' => 'dashboard.admin',
            'management' => 'dashboard.management', 
            'accountant' => 'dashboard.accountant',
            'loan_officer' => 'dashboard.loan-officer',
            'member' => 'dashboard.member',
            default => 'dashboard'
        };
    }
}