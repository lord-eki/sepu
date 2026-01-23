<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
       
        $phone = ltrim($request->phone, '0');
        $fullPhone = $request->country_code . $phone;

        //  Merge normalized phone back into request for validation
        $request->merge(['phone' => $fullPhone]);

        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'phone' => 'required|string|max:15|unique:users,phone',
            'country_code' => 'required|string|max:5',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Generate username (if needed)
        $username = User::generateUsername($request->name);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'username' => $username,
            'email' => $request->email,
            'phone' => $fullPhone,
            'password' => Hash::make($request->password),
            'role' => 'member',
        ]);

        event(new Registered($user));
        Auth::login($user);

        return to_route('dashboard');
        // return redirect()->route('verification.notice');
    }
}

