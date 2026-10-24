<?php

namespace App\Http\Middleware;

use App\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberAccess
{
    private const DEFAULT_STAFF = ['admin', 'management', 'accountant', 'loan_officer'];

    public function handle(Request $request, Closure $next, string ...$staffRoles): Response
    {
        $user = $request->user();
        abort_unless($user, 401);

        $staff = $staffRoles ?: self::DEFAULT_STAFF;

        if (in_array($user->role, $staff, true)) {
            return $next($request);
        }

        if ($user->role === 'member') {
            $param    = $request->route('member');
            $memberId = $param instanceof Member ? (int) $param->id : (int) $param;
            $ownId    = $user->member?->id;

            if ($ownId && $memberId === (int) $ownId) {
                return $next($request);
            }
        }

        abort(403, 'You are not allowed to access this record.');
    }
}