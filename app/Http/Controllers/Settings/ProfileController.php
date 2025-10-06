<?php

namespace App\Http\Controllers\Settings;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Member;
use App\Models\Account;
use Illuminate\Support\Facades\Log;


class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function checkEligibility()
{
    $user = Auth::user();
    $member = Member::where('user_id', $user->id)->first();

    if (!$member) {
        return response()->json(['eligible' => false, 'reason' => 'Profile incomplete']);
    }

    $hasRegFee = $member->transactions()->where('type', 'registration_fee')->sum('amount') >= 2500;
    $hasShareCapital = $member->accounts()->where('account_type', 'shares')->sum('balance') >= 5000;
    $hasShareDeposits = $member->accounts()->where('account_type', 'savings')->sum('balance') >= 5000;

    if ($hasRegFee && $hasShareCapital && $hasShareDeposits) {
        return response()->json(['eligible' => true]);
    }

    return response()->json([
        'eligible' => false,
        'reason' => 'Incomplete minimum requirements',
        'pending' => [
            'registration_fee' => $hasRegFee ? 0 : 2500,
            'share_capital' => $hasShareCapital ? 0 : 5000,
            'share_deposit' => $hasShareDeposits ? 0 : 5000,
        ],
    ]);
}


    public function complete()
    {
        return Inertia::render('Profile/Complete', [
            'user' => Auth::user(),
            'idTypes' => [
                'national_id' => 'National ID',
                'passport' => 'Passport',
                'driving_license' => 'Driving License',
            ],
            'genders' => [
                'male' => 'Male',
                'female' => 'Female',
                'other' => 'Other',
            ],
            'maritalStatuses' => [
                'single' => 'Single',
                'married' => 'Married',
                'divorced' => 'Divorced',
                'widowed' => 'Widowed',
            ],
        ]);
    }

     public function store(Request $request)
        {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name'  => 'required|string|max:255',
                'middle_name'  => 'nullable|string|max:255',
                'date_of_birth' => 'required|date',
                'gender' => 'required|string',
                'marital_status' => 'required|string',
                'id_type' => 'required|string',
                'id_number' => 'required|string|unique:members,id_number',
                'physical_address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'county' => 'required|string|max:255',
                'postal_address' => 'nullable|string|max:20',
                'occupation' => 'nullable|string|max:255',
                'employer' => 'nullable|string|max:255',
                'monthly_income' => 'nullable|numeric',
                'employee_number' => 'nullable|string|max:50',
                'emergency_contact_name' => 'required|string|max:255',
                'emergency_contact_phone' => 'required|string|max:20',
                'emergency_contact_relationship' => 'required|string|max:100',
                'profile_photo' => 'nullable|image|max:2048',
                'documents.*' => 'nullable|file|max:5120',
            ]);
            $user = Auth::user();
            if (Member::where('user_id', $user->id)->exists()) {
                return back()->withInput()->with('error', 'You have already completed your profile.');
            }

            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('members/photos', 'public');
                $validated['profile_photo'] = $path;
            }


            if ($request->hasFile('documents')) {
                $documentPaths = [];
                foreach ($request->file('documents') as $doc) {
                    $documentPaths[] = $doc->store('member_documents', 'public');
                }
                $validated['documents'] = $documentPaths; 
            }

            DB::beginTransaction();

            try {
                $member = Member::create(array_merge($validated, [
                    'user_id'           => Auth::id(),
                    'membership_id'     => $this->generateMembershipId(),
                    'membership_status' => 'inactive',
                    'membership_date'   => now(),
                ]));

                $member->accounts()->create([
                    'account_number' => $this->generateAccountNumber('SAV'),
                    'account_type'   => 'savings',
                    'balance'        => 0,
                    'available_balance' => 0,
                    'is_active'      => true,
                ]);

                $member->accounts()->create([
                    'account_number' => $this->generateAccountNumber('SHR'),
                    'account_type'   => 'shares',
                    'balance'        => 0,
                    'available_balance' => 0,
                    'is_active'      => true,
                ]);

                DB::commit();

                return redirect()->route('awaiting-activation')
                    ->with('success', 'Profile completed successfully! Please wait for approval.');
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Profile completion failed', ['error' => $e->getMessage()]);
                return back()->withInput()->with('error', 'Something went wrong while completing your profile. Please try again later.');
            }
        }


    public function updatePhoto(Request $request)
    {
        $user = Auth::user();
        $member = Member::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'profile_photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Delete old photo if it exists
        if ($member->profile_photo && \Storage::disk('public')->exists($member->profile_photo)) {
            \Storage::disk('public')->delete($member->profile_photo);
        }

        // Store new photo
        $path = $request->file('profile_photo')->store('members/photos', 'public');

        $member->update([
            'profile_photo' => $path,
        ]);

        return back()->with('success', 'Profile photo updated successfully.');
    }


    private function generateMembershipId(): string
    {
        do {
            $id = 'MEM' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Member::where('membership_id', $id)->exists());

        return $id;
    }

    private function generateAccountNumber(string $prefix): string
    {
        do {
            $number = $prefix . str_pad(random_int(1, 9999999), 7, '0', STR_PAD_LEFT);
        } while (DB::table('accounts')->where('account_number', $number)->exists());

        return $number;
    }

    /**
     * Show logged-in member's profile.
     */
    public function show(): Response
    {
        $user = Auth::user();

        $member = Member::where('user_id', $user->id)->firstOrFail();

        return Inertia::render('Profile/Show', [
            'user' => $user,
            'member' => $member,
        ]);
    }

    /**
     * Update logged-in member's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $member = Member::where('user_id', $user->id)->firstOrFail();

        try {
            $validated = $request->validate([
                'middle_name' => 'nullable|string|max:255',
                'occupation' => 'nullable|string|max:255',
                'employer'   => 'nullable|string|max:255',
                'monthly_income' => 'nullable|numeric|min:0',
                'physical_address' => 'nullable|string|max:255',
                'postal_address'   => 'nullable|string|max:255',
                'city'    => 'nullable|string|max:255',
                'county'  => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relationship' => 'nullable|string|max:255',
                'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
            ]);

            $member->update($validated);

            return back()->with('success', 'Profile updated successfully.');
        } catch (\Throwable $e) {
            \Log::error('Profile update failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

}


