<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $user = Auth::user();
        $requestedRole = $request->role;

        if ($user->role === 'member') {
            return back()->with('error', 'Members cannot switch roles.');
        }

        $allowedRoles = [$user->role, 'member'];

        if (!in_array($requestedRole, $allowedRoles)) {
            return back()->with('error', 'You are not allowed to switch to this role.');
        }

        session(['acting_as_role' => $requestedRole]);

        return back()->with('success', "Now acting as {$requestedRole}");
    }

    public function stop()
    {
        session()->forget('acting_as_role');

        return back()->with('success', 'Returned to your original role.');
    }
}
