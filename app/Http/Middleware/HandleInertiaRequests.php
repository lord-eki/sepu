<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Models\Member;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();
        $member = $user ? Member::where('user_id', $user->id)->first() : null;

        // Default available roles
        $roles = [];
        if ($user) {
            $roles[] = $user->role; // the permanent DB role
            if ($user->role !== 'member') {
                $roles[] = 'member'; // always allow switching to "member" view
            }
        }

        // Use active_role if set, otherwise fall back to their actual role
        $currentRole = $user?->active_role ?? $user?->role ?? 'member';



        return [
            ...parent::share($request),

            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],

            'auth' => [
                'user' => $user,
                'member' => $member,
                'roles' => $roles,
                'current_role' => $currentRole,
            ],

            'status' => fn () => $request->session()->get('status'),

            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],

            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',

            'can' => [
                'view-dashboard' => Gate::allows('view-dashboard'),
                'view-dividends' => Gate::allows('view-dividends'),
                'view-budgets' => Gate::allows('view-budgets'),
                'view-vouchers' => Gate::allows('view-vouchers'),
                'view-loans' => Gate::allows('view-loans'),
                'view-members' => Gate::allows('view-members'),
                'view-accounts' => Gate::allows('view-accounts'),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
