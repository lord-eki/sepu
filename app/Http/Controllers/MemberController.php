<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\StoreNextOfKinRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Requests\UpdateNextOfKinRequest;
use App\Models\Account;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\MemberNextOfKin;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LoanEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Exports\MembersExport;

class MemberController extends Controller
{
    /**
     * Show the import form
     */
    public function showImportForm()
    {
        return Inertia::render('Admin/Members/Import', [
            'sampleHeaders' => [
                'first_name', 'last_name', 'middle_name', 'date_of_birth', 'gender',
                'marital_status', 'email', 'phone', 'physical_address', 'postal_address',
                'city', 'county', 'id_type', 'id_number', 'occupation', 'employer',
                'monthly_income', 'emergency_contact_name', 'emergency_contact_phone',
                'emergency_contact_relationship',
            ],
        ]);
    }

    /**
     * Display a listing of members
     */
    public function index(Request $request): Response
    {
        $query = Member::with(['user', 'accounts', 'loans'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('membership_id', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('membership_status', $status);
            })
            ->when($request->sortBy, function ($query, $sortBy) use ($request) {
                $direction = $request->sortDirection ?? 'asc';
                $query->orderBy($sortBy, $direction);
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            });

        $members = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Members/Index', [
            'members' => $members,
            'filters' => $request->only(['search', 'status', 'sortBy', 'sortDirection']),
            'stats' => [
                'total' => Member::count(),
                'active' => Member::where('membership_status', 'active')->count(),
                'inactive' => Member::where('membership_status', 'inactive')->count(),
                'suspended' => Member::where('membership_status', 'suspended')->count(),
                'pending' => Member::where('membership_status', 'pending')->count(),
                'approved' => Member::where('membership_status', 'approved')->count(),
                'rejected' => Member::where('membership_status', 'rejected')->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new member
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Members/Create', [
            'membershipId' => $this->generateMembershipId(),
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

  
    /**
     * Store a newly created member
     */
    public function store(StoreMemberRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Password Logic (same as reset password)
            |--------------------------------------------------------------------------
            */
            $plainPassword =
                $request->password_type === 'custom'
                    ? $request->custom_password
                    : $request->id_number;

            if (!$plainPassword) {
                $plainPassword = Str::random(12);
            }

            /*
            |--------------------------------------------------------------------------
            | Create User Account
            |--------------------------------------------------------------------------
            */
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($plainPassword),
                'role' => 'member',
                'is_active' => true,

                // same reset-password logic
                'must_change_password' => $request->boolean('must_change_password', true),
                'password_changed_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Member Profile
            |--------------------------------------------------------------------------
            */
            $memberData = $request->validated();

            unset(
                $memberData['password_type'],
                $memberData['custom_password'],
                $memberData['must_change_password']
            );

            $memberData['user_id'] = $user->id;
            $memberData['membership_id'] = $this->generateMembershipId();
            $memberData['membership_date'] = now();
            $memberData['membership_status'] = 'active';

            /*
            |--------------------------------------------------------------------------
            | Profile Photo
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('profile_photo')) {
                $memberData['profile_photo'] = $request->file('profile_photo')
                    ->store('members/photos', 'public');
            }

            /*
            |--------------------------------------------------------------------------
            | Documents
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('documents')) {
                $documents = [];

                foreach ($request->file('documents') as $file) {
                    $path = $file->store('members/documents', 'public');

                    $documents[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType(),
                        'uploaded_at' => now(),
                    ];
                }

                $memberData['documents'] = json_encode($documents);
            }

            $member = Member::create($memberData);

            /*
            |--------------------------------------------------------------------------
            | Default Accounts
            |--------------------------------------------------------------------------
            */
            DB::table('accounts')->insert([
                [
                    'account_number' => $this->generateAccountNumber('SEPU', 'S'),
                    'account_type' => 'share_capital',
                    'balance' => 0,
                    'available_balance' => 0,
                    'is_active' => true,
                    'member_id' => $member->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'account_number' => $this->generateAccountNumber('SEPU', 'D'),
                    'account_type' => 'share_deposits',
                    'balance' => 0,
                    'available_balance' => 0,
                    'is_active' => true,
                    'member_id' => $member->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            DB::commit();

            return redirect()
                ->route('members.show', $member)
                ->with('success', 'Member created successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Failed to create member: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified member
     */
    public function show(Member $member): Response
    {
        $member->load([
            'user',
            'financeConfig.contributionAccount',
            'financeConfig.dividendAccount',
            'accounts' => function ($query) {
                $query->withSum('transactions', 'amount');
            },
            'loans' => function ($query) {
                $query->with('loanProduct')->latest();
            },
            'nextOfKin',
            'transactions' => function ($query) {
                $query->latest()->limit(10);
            },
            'dividends' => function ($query) {
                $query->with('dividend')->latest();
            },
        ]);

        $eligibilityService = new LoanEligibilityService;

        // Get a sample loan product for general eligibility check
        $sampleLoanProduct = LoanProduct::where('is_active', true)->first();
        $eligibilitySummary = null;

        if ($sampleLoanProduct) {
            $eligibilitySummary = $eligibilityService->checkEligibility(
                $member,
                $sampleLoanProduct,
                $sampleLoanProduct->min_amount
            );
        }

        return Inertia::render('Admin/Members/Show', [
            'member' => $member,
            'finance_configs' =>$member->financeConfig,
            'stats' => [
                'total_savings' => $member->accounts->where('account_type', 'share_deposits')->sum('balance'),
                'total_shares' => $member->accounts->where('account_type', 'share_capital')->sum('balance'),
                'total_loans' => $member->loans->where('status', 'active')->sum('outstanding_balance'),
                'total_dividends' => $member->dividends->sum('dividend_amount'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified member
     */
    public function edit(Member $member): Response
    {
        $member->load('user');

        return Inertia::render('Admin/Members/Edit', [
            'member' => $member,
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

    /**
     * Update the specified member
     */
    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Update user information
            $member->user->update([
                'name' => $request->first_name.' '.$request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            // Update member information
            $memberData = $request->validated();

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old photo
                if ($member->profile_photo) {
                    Storage::disk('public')->delete($member->profile_photo);
                }
                $memberData['profile_photo'] = $request->file('profile_photo')
                    ->store('members/photos', 'public');
            }

            // Handle documents upload
            if ($request->hasFile('documents')) {
                $existingDocuments = json_decode($member->documents, true) ?? [];
                $newDocuments = [];
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('members/documents', 'public');
                    $newDocuments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType(),
                        'uploaded_at' => now(),
                    ];
                }

                $memberData['documents'] = json_encode(array_merge($existingDocuments, $newDocuments));
            }

            $member->update($memberData);

            DB::commit();

            return redirect()->route('members.show', $member)
                ->with('success', 'Member updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getTraceAsString());

            return back()->withErrors(['error' => 'Failed to update member: '.$e->getMessage()]);
        }
    }

  /**
     * Remove the specified member
     */
    public function destroy(Member $member): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Only allow deleting INACTIVE, REJECTED, or SUSPENDED members
            if (!in_array($member->membership_status, ['inactive', 'rejected', 'suspended'])) {
                return redirect()->route('members.show', $member->id)
                    ->with('error', 'Only inactive, rejected, or suspended members can be deleted.');
            }

            // Prevent delete if member still has any loans
            if ($member->loans()->exists()) {
                return redirect()->route('members.show', $member->id)
                    ->with('error', 'Cannot delete member with loans.');
            }

            // Prevent delete if member has balances
            if ($member->accounts()->where('balance', '>', 0)->exists()) {
                return redirect()->route('members.show', $member->id)
                    ->with('error', 'Cannot delete member with account balances.');
            }

            // Soft delete member
            $member->delete();

            // Deactivate login
            if ($member->user) {
                $member->user->update(['is_active' => false]);
            }

            DB::commit();

            return redirect()->route('members.index')
                ->with('success', 'Member deleted successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()->route('members.show', $member->id)
                ->with('error', 'Failed to delete member: ' . $e->getMessage());
        }
    }


    /**
     * T
     * Permanently removes a member and all related data.
     */
    public function bulkDelete(Request $request)
    {
        $memberIds = $request->member_ids;
    
        if (empty($memberIds)) {
            return redirect()->back()->with('error', 'No members selected for deletion.');
        }
    
        $currentUserId = auth()->id(); // Logged-in user
        $currentMember = Member::where('user_id', $currentUserId)->first(); // The member record of logged-in admin
    
        // Prevent self-deletion
        if ($currentMember && in_array($currentMember->id, $memberIds)) {
    
            // Remove the admin from the deletion list
            $memberIds = array_diff($memberIds, [$currentMember->id]);
    
            // If admin was the only selected member
            if (empty($memberIds)) {
                return redirect()->back()->with('error', "You cannot delete your own account.");
            }
        }
    
        try {
            DB::beginTransaction();
    
            $members = Member::with(['user', 'loans', 'accounts', 'dividends', 'nextOfKin', 'transactions'])
                ->whereIn('id', $memberIds)
                ->get();
    
            foreach ($members as $member) {
    
                // Delete profile photo
                if ($member->profile_photo) {
                    Storage::disk('public')->delete($member->profile_photo);
                }
    
                // Delete member documents
                if (!empty($member->documents)) {
                    $docs = json_decode($member->documents, true);
                    foreach ($docs as $doc) {
                        Storage::disk('public')->delete($doc['path']);
                    }
                }
    
                // Delete transactions
                $member->transactions()->delete();
    
                // Delete loans + attachments
                foreach ($member->loans as $loan) {
                    if (!empty($loan->attachments)) {
                        foreach (json_decode($loan->attachments, true) as $file) {
                            Storage::disk('public')->delete($file['path']);
                        }
                    }
                    $loan->delete();
                }
    
                // Delete next of kin
                $member->nextOfKin()->delete();
    
                // Delete dividends
                $member->dividends()->delete();
    
                // Delete accounts
                $member->accounts()->delete();
    
                // Delete associated user
                if ($member->user) {
                    $member->user->delete();
                }
    
                // Finally delete member record
                $member->forceDelete();
            }
    
            DB::commit();
    
            return redirect()->route('members.index')
                ->with('success', 'Selected members permanently deleted.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Bulk delete failed: ' . $e->getMessage());
        }
    }
    
   /**
     * Approve a member (before activation)
     */
    public function approve(Member $member): RedirectResponse
    {
        // Only pending members can be approved
        if ($member->membership_status !== 'pending') {
            return back()->with('error', 'This member cannot be approved.');
        }

        // Approve member
        $member->update([
            'membership_status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Member approved successfully.');
    }

    /**
     * Check payment after registration checking process
     */
    private function hasCompletedActivationPayment(Member $member): bool
    {
        $member->load('accounts');

        $hasShareDeposits = $member->accounts->contains(fn ($a) =>
            $a->account_type === 'share_deposits' && $a->available_balance >= 7500
        );

        $hasShareCapital = $member->accounts->contains(fn ($a) =>
            $a->account_type === 'share_capital' && $a->available_balance >= 5000
        );

        return $hasShareDeposits && $hasShareCapital;
    }



    public function confirmPayment(Request $request)
    {
        $member = $request->user()->member;

        if (! $member) {
            return back()->with('error', 'Member record not found.');
        }

        if ($member->membership_status === 'pending') {
            return back()->with('error', 'Your membership is still under review.');
        }

        if (! in_array($member->membership_status, ['approved', 'active'])) {
            return back()->with('error', 'Your membership is not ready for activation.');
        }

        if (! $this->hasCompletedActivationPayment($member)) {
            return back()->with(
                'error',
                'Payment not yet reflected. Please complete the required payments.'
            );
        }

        // Payment verified
        if ($member->membership_status === 'active') {
            // Redirect to dashboard immediately
            return redirect()->route('dashboard')
                            ->with('success', 'Payment verified. Your account is active!');
        }

        // Payment verified but not activated
        return back()->with([
            'success' => 'Payment verified successfully. Click Finish to check activation.',
            'activated' => false,
        ]);
    }

    public function checkActivation(Request $request)
    {
        $member = $request->user()->member;

        if (! $member) {
            return back()->with('error', 'Member record not found.');
        }

        if ($member->membership_status === 'active') {
            // Redirect automatically if active
            return redirect()->route('dashboard')
                            ->with('success', 'Your account is now active!');
        }

        return back()->with([
            'error' => 'Your account is still awaiting activation. Please wait.',
            'activated' => false,
        ]);
    }


     /**
     * Activate a member
     */
    public function activate(Member $member): RedirectResponse
    {
        // Eager load accounts
        $member->load('accounts');

        // Allow activation only for approved or previously active (suspended/inactive) members
        $allowedStatuses = ['approved', 'inactive', 'suspended', 'active'];

        if (!in_array($member->membership_status, $allowedStatuses)) {
            return back()->with('error', 'This member cannot be activated.');
        }

        // Check if member has already fully paid for activation
        $hasShareDeposits = $member->accounts->contains(function ($account) {
            return $account->account_type === 'share_deposits' && $account->available_balance >= 7500;
        });

        $hasShareCapital = $member->accounts->contains(function ($account) {
            return $account->account_type === 'share_capital' && $account->available_balance >= 5000;
        });

        $hasPaid = $hasShareDeposits && $hasShareCapital;

        // If member hasn’t paid yet, prevent activation
        if (! $hasPaid) {
            return back()->with('error', 'Member has not completed the required payment for activation.');
        }

        // Update member to active
        $member->update(['membership_status' => 'active']);
        $member->user?->update(['is_active' => true]);

        return back()->with('success', 'Member activated successfully.');
    }


        /**
         * Deactivate an active member
         */
        public function deactivate(Member $member): RedirectResponse
        {
            if ($member->membership_status !== 'active') {
                return back()->with('error', 'Only active members can be deactivated.');
            }

            $member->update(['membership_status' => 'inactive']);
            $member->user?->update(['is_active' => false]);

            return back()->with('success', 'Member deactivated successfully.');
        }

        /**
         * Reject a pending member
         */
        public function reject(Member $member): RedirectResponse
        {
            if ($member->membership_status !== 'pending') {
                return back()->with('error', 'Only pending members can be rejected.');
            }

            $member->update(['membership_status' => 'rejected']);
            $member->user?->update(['is_active' => false]);

            return back()->with('success', 'Member rejected successfully.');
        }

        /**
         * Suspend an active member
         */
        public function suspend(Member $member): RedirectResponse
        {
            if ($member->membership_status !== 'active') {
                return back()->with('error', 'Only active members can be suspended.');
            }

            $member->update(['membership_status' => 'suspended']);
            $member->user?->update(['is_active' => false]);

            return back()->with('success', 'Member suspended successfully.');
        }


    /**
     * Get member accounts
     */
    public function accounts(Member $member): Response
    {
        $accounts = $member->accounts()->with(['transactions' => function ($query) {
            $query->latest()->limit(5);
        }])->get();

        return Inertia::render('Members/Accounts', [
            'member' => $member,
            'accounts' => $accounts,
        ]);
    }

    public function showDeposit(Member $member, Account $account)
    {
        $this->authorizeAccount($member, $account);

        return Inertia::render('Accounts/Deposit', [
            'account' => $account->load('member'),
            'paymentMethods' => [
                'cash' => 'Cash',
                'mobile_money' => 'Mobile Money',
                'bank_transfer' => 'Bank Transfer',
                'cheque' => 'Cheque',
            ],
            'authUser' => auth()->user(),
        ]);
    }

    public function deposit(Request $request, Member $member, Account $account)
    {
        $this->authorizeAccount($member, $account);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
        ]);

        // Record old balance
        $balanceBefore = $account->balance;

        // Update account balance
        $account->balance += $request->amount;
        $account->save();

        // Create transaction with before/after balances
        Transaction::create([
            'transaction_id' => 'TXN'.now()->format('Ymd').strtoupper(Str::random(6)),
            'account_id' => $account->id,
            'member_id' => $member->id,
            'amount' => $request->amount,
            'transaction_type' => 'deposit',
            'description' => $request->payment_method,
            'status' => 'completed',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
            'balance_before' => $balanceBefore,
            'balance_after' => $account->balance,
        ]);

        return back()->with('success', 'Deposit successful!');
    }

    public function showWithdrawal(Member $member, Account $account)
    {
        $this->authorizeAccount($member, $account);

        return Inertia::render('Accounts/Withdrawal', [
            'account' => $account->load('member'),
            'paymentMethods' => [
                'cash' => 'Cash',
                'mobile_money' => 'Mobile Money',
                'bank_transfer' => 'Bank Transfer',
                'cheque' => 'Cheque',
            ],
            'authUser' => auth()->user(),
        ]);
    }

    public function withdraw(Request $request, Member $member, Account $account)
    {
        $this->authorizeAccount($member, $account);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
        ]);

        // Check sufficient funds
        if ($request->amount > $account->balance) {
            return back()->withErrors(['amount' => 'Insufficient balance for this withdrawal.']);
        }

        $balanceBefore = $account->balance;

        // Deduct from balance
        $account->balance -= $request->amount;
        $account->save();

        // Record transaction
        Transaction::create([
            'transaction_id' => 'TXN'.now()->format('Ymd').strtoupper(Str::random(6)),
            'account_id' => $account->id,
            'member_id' => $member->id,
            'amount' => $request->amount,
            'transaction_type' => 'withdrawal',
            'description' => $request->payment_method,
            'status' => 'completed',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
            'balance_before' => $balanceBefore,
            'balance_after' => $account->balance,
        ]);

        return redirect()->route('members.accounts', $member)
            ->with('success', 'Withdrawal successful.');
    }

    private function generateTransactionId(): string
    {
        do {
            $id = 'TXN'.date('Ymd').strtoupper(Str::random(6));
        } while (Transaction::where('transaction_id', $id)->exists());

        return $id;
    }

    protected function authorizeAccount(Member $member, Account $account)
    {
        if ($account->member_id !== $member->id) {
            abort(403, 'Unauthorized action.');
        }
    }

      /**
         * Assign usernames to members
     */

    public function assignUsernames(Request $request)
    {
        $request->validate([
            'member_ids' => 'required|array',
        ]);

        $members = Member::whereIn('id', $request->member_ids)->get();

        foreach ($members as $member) {
            // Skip if username already exists
            if (!$member->user->username) {
                $username = \App\Models\User::generateUsername($member->first_name . ' ' . $member->last_name);
                $member->user->update(['username' => $username]);
            }
        }

        return back()->with('success', 'Username generated successfully.');
    }


   public function resetPassword(Request $request, Member $member)
    {
        $request->validate([
            'password_type'         => 'required|in:default,custom',
            'custom_password'      => 'nullable|required_if:password_type,custom|min:6',
            'must_change_password' => 'boolean',
        ]);

        $user = $member->user;

        if (!$user) {
            return back()->with('error', 'User not found');
        }

        // Choose password
        $newPassword = $request->password_type === 'custom'
            ? $request->custom_password
            : $member->id_number;

        $user->password = Hash::make($newPassword);

        // Force change next login
        $user->must_change_password = $request->boolean('must_change_password', true);

        // Optional audit timestamp
        $user->password_changed_at = now();

        $user->save();

        return back()->with('success', 'Password reset successfully');
    }

    public function updateUsername(Request $request, Member $member)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username,' . $member->user_id,
        ]);

        $user = $member->user;

        if (!$user) {
            return back()->with('error', 'User not found');
        }

        $user->username = $request->username;
        $user->save();

        return back()->with('success', 'Username updated successfully');
    }

    /**
     * Get member transactions
     */
    public function transactions(Member $member, Request $request): Response
    {
        $transactions = $member->transactions()
            ->with(['account', 'processedBy'])
            ->when($request->type, function ($query, $type) {
                $query->where('transaction_type', $type);
            })
            ->when($request->account_id, function ($query, $accountId) {
                $query->where('account_id', $accountId);
            })
            ->latest()
            ->paginate(15);

        return Inertia::render('Members/Transactions', [
            'member' => $member,
            'transactions' => $transactions,
            'accounts' => $member->accounts,
            'filters' => $request->only(['type', 'account_id']),
        ]);
    }

    /**
     * Get member loans
     */
    public function loans(Member $member): Response
    {
        $loans = $member->loans()
            ->with(['loanProduct', 'guarantors.guarantorMember', 'repayments'])
            ->latest()
            ->get();

        return Inertia::render('Members/Loans', [
            'member' => $member,
            'loans' => $loans,
        ]);
    }

    /**
     * Get member loan eligibility
     */
    public function loanEligibility(Member $member, Request $request)
    {
        $loanProducts = LoanProduct::where('is_active', true)->get();

        if ($request->has('loan_product_id') && $request->has('requested_amount')) {
            $loanProduct = LoanProduct::findOrFail($request->loan_product_id);

            $eligibilityService = new LoanEligibilityService;
            $eligibility = $eligibilityService->checkEligibility(
                $member,
                $loanProduct,
                $request->requested_amount
            );

            $maxLoanAmount = $eligibilityService->getMaximumLoanAmount($member, $loanProduct);

            return response()->json([
                'success' => true,
                'data' => array_merge($eligibility, [
                    'max_loan_amount' => $maxLoanAmount,
                ]),
            ]);
        }

        return Inertia::render('Members/LoanEligibility', [
            'member' => $member,
            'loanProducts' => $loanProducts,
        ]);
    }

    /**
     * Get member dividends
     */
    public function dividends(Member $member): Response
    {
        $dividends = $member->dividends()
            ->with('dividend')
            ->latest()
            ->get();

        return Inertia::render('Members/Dividends', [
            'member' => $member,
            'dividends' => $dividends,
        ]);
    }

    /**
     * Get member guarantees
     */
    public function guarantees(Member $member): Response
    {
        $guarantees = $member->guaranteedLoans()
            ->with(['loan.member', 'loan.loanProduct'])
            ->latest()
            ->get();

        return Inertia::render('Members/Guarantees', [
            'member' => $member,
            'guarantees' => $guarantees,
        ]);
    }

    /**
     * Get member next of kin
     */
    public function nextOfKin(Member $member): Response
    {
        $nextOfKin = $member->nextOfKin()->get();

        return Inertia::render('Admin/Members/NextOfKin', [
            'member' => $member,
            'nextOfKin' => $nextOfKin,
        ]);
    }

    /**
     * Store next of kin
     */
    public function storeNextOfKin(StoreNextOfKinRequest $request, Member $member): RedirectResponse
    {
        try {
            $member->nextOfKin()->create($request->validated());
            return back()->with('success', 'Next of kin added successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add next of kin');
        }
    }


    /**
     * Update next of kin
     */
    public function updateNextOfKin(UpdateNextOfKinRequest $request, Member $member, MemberNextOfKin $nextOfKin): RedirectResponse
    {
        $nextOfKin->update($request->validated());

        return back()->with('success', 'Next of kin updated successfully');
    }

    /**
     * Delete next of kin
     */
    public function destroyNextOfKin(Member $member, MemberNextOfKin $nextOfKin): RedirectResponse
    {
        $nextOfKin->delete();

        return back()->with('success', 'Next of kin deleted successfully');
    }

    /**
     * Upload member documents
     */
    public function uploadDocuments(Request $request, Member $member): RedirectResponse
    {
        $request->validate([
            'documents.*' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        if ($request->hasFile('documents')) {
            $existingDocuments = json_decode($member->documents, true) ?? [];
            $newDocuments = [];
            foreach ($request->file('documents') as $file) {
                $path = $file->store('members/documents', 'public');
                $newDocuments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                    'uploaded_at' => now(),
                ];
            }

            $member->update([
                'documents' => json_encode(array_merge($existingDocuments, $newDocuments)),
            ]);
        }

        return back()->with('success', 'Documents uploaded successfully');
    }

    /**
     * Delete member document
     */
    public function deleteDocument(Member $member, $documentIndex): RedirectResponse
    {
        $documents = json_decode($member->documents, true) ?? [];

        if (isset($documents[$documentIndex])) {
            // Delete file from storage
            Storage::disk('public')->delete($documents[$documentIndex]['path']);

            // Remove from array
            unset($documents[$documentIndex]);

            // Reindex array
            $documents = array_values($documents);

            $member->update(['documents' => json_encode($documents)]);
        }

        return back()->with('success', 'Document deleted successfully');
    }

    /**
     * Search members (API endpoint)
     */
    public function searchMembers(Request $request)
    {
        $query = $request->get('q');

        $members = Member::with('user')
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('membership_id', 'like', "%{$query}%")
                    ->orWhere('id_number', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->first_name.' '.$member->last_name,
                    'membership_id' => $member->membership_id,
                    'email' => $member->user->email,
                    'phone' => $member->user->phone,
                ];
            });

        return response()->json($members);
    }

    /**
     * Get member statistics
     */
    public function memberStats()
    {
        return response()->json([
            'total' => Member::count(),
            'active' => Member::where('membership_status', 'active')->count(),
            'inactive' => Member::where('membership_status', 'inactive')->count(),
            'suspended' => Member::where('membership_status', 'suspended')->count(),
            'new_this_month' => Member::whereMonth('created_at', now()->month)->count(),
            'gender_distribution' => Member::select('gender', DB::raw('count(*) as count'))
                ->groupBy('gender')->get(),
            'age_distribution' => Member::select(
                DB::raw('CASE 
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 25 THEN "Under 25"
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 25 AND 35 THEN "25-35"
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 36 AND 50 THEN "36-50"
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) > 50 THEN "Over 50"
                    ELSE "Unknown"
                END as age_group'),
                DB::raw('count(*) as count')
            )->groupBy('age_group')->get(),
        ]);
    }

    /**
     * Validate member ID
     */
    public function validateMemberId(Request $request)
    {
        $exists = Member::where('membership_id', $request->membership_id)->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * Export members
     */
    public function exportMembers(Request $request)
    {
        $memberIds = $request->input('member_ids', []);

        $export = new MembersExport($memberIds);
        return $export->download();
    }

    public function bulkImport(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        try {
            // Increase execution time and memory limit for large imports
            set_time_limit(300); // 5 minutes
            ini_set('memory_limit', '512M');

            $file = $request->file('file');

            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Normalize headers
            $headers = array_map(fn($header) => trim(strtolower(str_replace(' ', '_', $header))), $rows[0]);

            // Remove header row
            array_shift($rows);

            $successCount = 0;
            $errorCount = 0;
            $skippedCount = 0;
            $errors = [];

            // Process in chunks
            $chunks = array_chunk($rows, 50);

            foreach ($chunks as $chunkIndex => $chunk) {
                DB::beginTransaction();

                try {
                    foreach ($chunk as $index => $row) {
                        $rowNumber = ($chunkIndex * 50) + $index + 2;

                        // Skip empty rows
                        if (empty(array_filter($row))) {
                            $skippedCount++;
                            continue;
                        }

                        try {
                            // Map row data to headers
                            $data = array_combine($headers, $row);
                            $preparedData = $this->prepareImportData($data);

                            // Required fields
                            if (empty($preparedData['first_name'])) {
                                throw new \Exception('First name is required');
                            }
                            if (empty($preparedData['last_name'])) {
                                throw new \Exception('Last name is required');
                            }
                            if (empty($preparedData['id_type'])) {
                                throw new \Exception('ID type is required');
                            }

                            // Generate missing fields
                            $preparedData['id_number'] ??= $this->generateIdNumber();
                            $email = $preparedData['email'] ?? $this->generateEmail($preparedData['first_name'], $preparedData['last_name']);
                            $phone = $preparedData['phone'] ?? $this->generatePhone();
                            $fullName = trim($preparedData['first_name'].' '.$preparedData['last_name']);
                            $username = User::generateUsername($fullName);

                            // Validate row
                            $validator = Validator::make($preparedData, [
                                'first_name' => 'required|string|max:255',
                                'last_name' => 'required|string|max:255',
                                'middle_name' => 'nullable|string|max:255',
                                'date_of_birth' => 'nullable|date',
                                'gender' => 'nullable|in:male,female,other',
                                'marital_status' => 'nullable|in:single,married,divorced,widowed',
                                'email' => 'nullable|email|unique:users,email',
                                'phone' => 'nullable|string|max:20|unique:users,phone',
                                'id_type' => 'required|in:national_id,passport,driving_license',
                                'id_number' => 'required|string|unique:members,id_number',
                            ]);

                            if ($validator->fails()) {
                                $errorCount++;
                                $errors[] = [
                                    'row' => $rowNumber,
                                    'name' => $fullName ?: 'Unknown Member',
                                    'errors' => $validator->errors()->all(),
                                ];
                                continue;
                            }

                            // Create user
                            $user = User::create([
                                'name' => $fullName,
                                'username' => $username,
                                'email' => $email,
                                'phone' => $phone,
                                'password' => Hash::make('password123'),
                                'role' => 'member',
                                'is_active' => true,
                            ]);

                            // Create member
                            $member = Member::create([
                                'user_id' => $user->id,
                                'membership_id' => $this->generateMembershipId(),
                                'first_name' => $preparedData['first_name'],
                                'last_name' => $preparedData['last_name'],
                                'middle_name' => $preparedData['middle_name'] ?? null,
                                'date_of_birth' => $preparedData['date_of_birth'] ?? '1990-01-01',
                                'gender' => $preparedData['gender'] ?? 'male',
                                'marital_status' => $preparedData['marital_status'] ?? 'single',
                                'physical_address' => $preparedData['physical_address'] ?? 'Not provided',
                                'postal_address' => $preparedData['postal_address'] ?? 'Not provided',
                                'city' => $preparedData['city'] ?? 'Not provided',
                                'county' => $preparedData['county'] ?? 'Nairobi',
                                'country' => 'Kenya',
                                'id_type' => $preparedData['id_type'],
                                'id_number' => $preparedData['id_number'],
                                'occupation' => $preparedData['occupation'] ?? null,
                                'employer' => $preparedData['employer'] ?? null,
                                'monthly_income' => $preparedData['monthly_income'] ?? null,
                                'emergency_contact_name' => $preparedData['emergency_contact_name'] ?? 'Not provided',
                                'emergency_contact_phone' => $preparedData['emergency_contact_phone'] ?? 'Not provided',
                                'emergency_contact_relationship' => $preparedData['emergency_contact_relationship'] ?? 'Not specified',
                                'membership_date' => now(),
                                'membership_status' => 'active',
                            ]);

                            // Create accounts
                            DB::table('accounts')->insert([
                                [
                                    'account_number' => $this->generateAccountNumber('SEPU', 'S'),
                                    'account_type' => 'share_capital',
                                    'balance' => 0,
                                    'available_balance' => 0,
                                    'is_active' => true,
                                    'member_id' => $member->id,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ],
                                [
                                    'account_number' => $this->generateAccountNumber('SEPU', 'D'),
                                    'account_type' => 'share_deposits',
                                    'balance' => 0,
                                    'available_balance' => 0,
                                    'is_active' => true,
                                    'member_id' => $member->id,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            ]);

                            $successCount++;

                        } catch (\Exception $e) {
                            $errorCount++;
                            $errors[] = [
                                'row' => $rowNumber,
                                'name' => $preparedData['first_name'] ?? 'Unknown Member',
                                'errors' => [$e->getMessage()],
                            ];
                            \Log::error("Import failed at row {$rowNumber}: ".$e->getMessage());
                        }
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Import chunk failed: '.$e->getMessage());
                }
            }

            $message = "Import completed: {$successCount} members imported successfully";
            if ($errorCount > 0) $message .= ", {$errorCount} failed";
            if ($skippedCount > 0) $message .= ", {$skippedCount} skipped (empty rows)";

            return redirect()->route('members.import.form')
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            return redirect()->route('members.import.form')
                ->with('error', 'Import failed: '.$e->getMessage())
                ->with('import_errors', $errors);
        }
    }

    /**
     * Prepare and clean import data
     */
    private function prepareImportData(array $data): array
    {
        // Map possible header variations
        $firstName = $data['first_name'] ?? $data['firstname'] ?? '';
        $lastName = $data['last_name'] ?? $data['lastname'] ?? '';
        $middleName = $data['middle_name'] ?? $data['middlename'] ?? '';

        return [
            'first_name' => trim($firstName),
            'last_name' => trim($lastName),
            'middle_name' => trim($middleName),
            'date_of_birth' => $this->parseDate($data['date_of_birth'] ?? $data['dob'] ?? null),
            'gender' => strtolower(trim($data['gender'] ?? '')),
            'marital_status' => strtolower(trim($data['marital_status'] ?? $data['maritalstatus'] ?? '')),
            'email' => trim($data['email'] ?? ''),
            'phone' => $this->cleanPhone($data['phone'] ?? $data['phone_number'] ?? ''),
            'physical_address' => trim($data['physical_address'] ?? $data['address'] ?? ''),
            'postal_address' => trim($data['postal_address'] ?? ''),
            'city' => trim($data['city'] ?? ''),
            'county' => trim($data['county'] ?? ''),
            'id_type' => strtolower(trim($data['id_type'] ?? $data['idtype'] ?? 'national_id')),
            'id_number' => trim($data['id_number'] ?? $data['idnumber'] ?? ''),
            'occupation' => trim($data['occupation'] ?? ''),
            'employer' => trim($data['employer'] ?? ''),
            'monthly_income' => $this->parseNumeric($data['monthly_income'] ?? $data['income'] ?? null),
            'emergency_contact_name' => trim($data['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => $this->cleanPhone($data['emergency_contact_phone'] ?? ''),
            'emergency_contact_relationship' => strtolower(trim($data['emergency_contact_relationship'] ?? '')),
        ];
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            // Handle Excel numeric dates
            if (is_numeric($date)) {
                $unixDate = ($date - 25569) * 86400;

                return date('Y-m-d', $unixDate);
            }

            // Try to parse string dates
            $timestamp = strtotime($date);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }

            return null;
        } catch (\Exception $e) {

            return null;
        }
    }

    /**
     * Clean phone number
     */
    private function cleanPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        return $phone;
    }

    /**
     * Parse numeric value
     */
    private function parseNumeric($value): ?float
    {
        if (empty($value)) {
            return null;
        }

        // Remove any non-numeric characters except decimal point
        $cleaned = preg_replace('/[^0-9.]/', '', $value);

        return $cleaned ? (float) $cleaned : null;
    }

    /**
     * Generate email if not provided
     */
    private function generateEmail(string $firstName, string $lastName): string
    {
        $baseEmail = strtolower(Str::slug($firstName.'.'.$lastName)).'@sepu.sacco.ke';
        $email = $baseEmail;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = strtolower(Str::slug($firstName.'.'.$lastName)).$counter.'@sepu.sacco.ke';
            $counter++;
        }

        return $email;
    }

    /**
     * Generate unique phone number if not provided
     */
    private function generatePhone(): string
    {
        do {
            $phone = '+2547'.str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        } while (User::where('phone', $phone)->exists());

        return $phone;
    }

    /**
     * Generate unique ID number if not provided
     */
    private function generateIdNumber(): string
    {
        do {
            $idNumber = 'GEN'.str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        } while (Member::where('id_number', $idNumber)->exists());

        return $idNumber;
    }

    /**
     * Download sample template
     */
    public function downloadTemplate()
    {
        $headers = [
            'first_name', 'last_name', 'middle_name', 'date_of_birth', 'gender',
            'marital_status', 'email', 'phone', 'physical_address', 'postal_address',
            'city', 'county', 'id_type', 'id_number', 'occupation', 'employer',
            'monthly_income', 'emergency_contact_name', 'emergency_contact_phone',
            'emergency_contact_relationship',
        ];

        $sampleData = [
            [
                'John', 'Doe', 'Smith', '1990-01-15', 'male',
                'married', 'john.doe@example.com', '+254712345678', '123 Main St',
                'P.O. Box 123', 'Nairobi', 'Nairobi', 'national_id', '12345678',
                'Teacher', 'ABC School', '50000', 'Jane Doe', '+254712345679',
                'Spouse',
            ],
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->fromArray($headers, null, 'A1');

        // Add sample data
        $sheet->fromArray($sampleData, null, 'A2');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0A2342'],
            ],
            'font' => ['color' => ['rgb' => 'FFFFFF']],
        ];
        $sheet->getStyle('A1:T1')->applyFromArray($headerStyle);

        // Auto-size columns
        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $fileName = 'members_import_template_'.date('Y-m-d').'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Generate unique membership ID in format SEPU/SACCO/XXX
     */
    private function generateMembershipId(): string
    {
        // Get the last member ordered by ID
        $lastMember = Member::orderBy('id', 'desc')->first();

        if (! $lastMember) {
            // First member
            $number = 1;
        } else {
            // Extract number from last membership ID
            $lastId = $lastMember->membership_id;
            preg_match('/SEPU\/SACCO\/(\d+)/', $lastId, $matches);

            if (isset($matches[1])) {
                $number = intval($matches[1]) + 1;
            } else {
                $number = Member::count() + 1;
            }
        }

        return 'SEPU/SACCO/'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique account number
     */
    private function generateAccountNumber(string $prefix = 'SEPU', string $suffix = 'S'): string
    {
        do {
            // Get the last account for this type
            $lastAccount = DB::table('accounts')
                ->where('account_number', 'like', $prefix.'%'.$suffix)
                ->orderBy('id', 'desc')
                ->first();

            if ($lastAccount) {
                // Extract the number from the last account (e.g., SEPU0001S -> 0001)
                preg_match('/'.$prefix.'(\d+)'.$suffix.'/', $lastAccount->account_number, $matches);
                $number = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
            } else {
                $number = 1;
            }

            $accountNumber = $prefix.str_pad($number, 4, '0', STR_PAD_LEFT).$suffix;
        } while (DB::table('accounts')->where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

   // DEPOSITS
/**
 * Import deposits for existing members
 */
public function importDeposits(Request $request): RedirectResponse
{
    \Log::info('=== DEPOSIT IMPORT STARTED ===');

    $request->validate([
        'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        'year' => 'required|integer|min:2020|max:2030',
    ]);

    try {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $file = $request->file('file');
        $year = $request->year;

        \Log::info('Import parameters', [
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'year' => $year,
            'user_id' => auth()->id(),
        ]);

        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestDataRow();
        $rows = $worksheet->rangeToArray('A1:' . $worksheet->getHighestDataColumn() . $highestRow);

        // Get headers and normalize
        $headers = array_map(fn($header) => trim(strtolower(str_replace(' ', '_', $header ?? ''))), $rows[0]);
        array_shift($rows); // remove header row

        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        $errors = [];
        $totalDepositsProcessed = 0;

        $monthColumns = ['jan','feb','mar','april','may','june','july','aug','sept','oct','nov','dec'];
        $monthColumnIndices = [];
        foreach ($headers as $index => $header) {
            if (in_array($header, $monthColumns)) {
                $monthColumnIndices[$header] = $index;
            }
        }

        // Find name column index
        $nameColumnIndex = array_search('name', $headers);
        if ($nameColumnIndex === false) {
            $nameColumnIndex = array_search('no._name', $headers);
        }
        if ($nameColumnIndex === false) {
            $nameColumnIndex = array_search('no.name', $headers);
        }
        if ($nameColumnIndex === false) {
            foreach ($headers as $index => $header) {
                if (strpos($header, 'name') !== false) {
                    $nameColumnIndex = $index;
                    break;
                }
            }
        }
        if ($nameColumnIndex === false) {
            return back()->withErrors(['error' => 'Name column not found in spreadsheet']);
        }

        // Find shares column
        $sharesColumnIndex = array_search('shares', $headers);

        // Filter out completely empty rows
        $rows = array_filter($rows, fn($row) => !empty(array_filter($row, fn($cell) => !is_null($cell) && trim($cell) !== '')));

        $chunks = array_chunk($rows, 50);

        foreach ($chunks as $chunkIndex => $chunk) {
            DB::beginTransaction();
            try {
                foreach ($chunk as $index => $row) {
                    $rowNumber = ($chunkIndex * 50) + $index + 2;
                    $memberName = trim($row[$nameColumnIndex] ?? '');

                    if (empty($memberName)) {
                        $skippedCount++;
                        $errors[] = [
                            'row' => $rowNumber,
                            'name' => 'Unknown Member',
                            'errors' => ['Name is missing'],
                        ];
                        continue;
                    }

                    try {
                        $member = $this->findMemberByName($memberName);

                        if (!$member) {
                            $errorCount++;
                            $errors[] = [
                                'row' => $rowNumber,
                                'name' => $memberName,
                                'errors' => ['Member not found in system'],
                            ];
                            continue;
                        }

                        // Share capital account
                        $shareCapitalAccount = Account::firstOrCreate([
                            'member_id' => $member->id,
                            'account_type' => 'share_capital',
                        ], [
                            'account_number' => $this->generateAccountNumber('SEPU', 'S'),
                            'balance' => 0,
                            'available_balance' => 0,
                            'is_active' => true,
                        ]);

                        // Initial shares
                        if ($sharesColumnIndex !== false) {
                            $initialShares = $this->parseNumeric($row[$sharesColumnIndex] ?? null);
                            if ($initialShares && $initialShares > 0) {
                                $existingOpeningBalance = Transaction::where('account_id', $shareCapitalAccount->id)
                                    ->where('transaction_type', 'deposit')
                                    ->where('description', 'LIKE', '%Opening balance%')
                                    ->exists();

                                if (!$existingOpeningBalance) {
                                    $balanceBefore = $shareCapitalAccount->balance;
                                    $balanceAfter = $balanceBefore + $initialShares;

                                    Transaction::create([
                                        'transaction_id' => $this->generateTransactionId(),
                                        'account_id' => $shareCapitalAccount->id,
                                        'member_id' => $member->id,
                                        'transaction_type' => 'deposit',
                                        'amount' => $initialShares,
                                        'balance_before' => $balanceBefore,
                                        'balance_after' => $balanceAfter,
                                        'description' => "Initial share capital - Opening balance {$year}",
                                        'status' => 'completed',
                                        'processed_by' => auth()->id(),
                                        'processed_at' => "{$year}-01-01",
                                        'created_at' => "{$year}-01-01",
                                    ]);

                                    $shareCapitalAccount->update([
                                        'balance' => $balanceAfter,
                                        'available_balance' => $balanceAfter,
                                    ]);
                                }
                            }
                        }

                        // Share deposits account
                        $shareDepositsAccount = Account::firstOrCreate([
                            'member_id' => $member->id,
                            'account_type' => 'share_deposits',
                        ], [
                            'account_number' => $this->generateAccountNumber('SEPU', 'D'),
                            'balance' => 0,
                            'available_balance' => 0,
                            'is_active' => true,
                        ]);

                        $memberDepositsCount = 0;
                        $currentBalance = $shareDepositsAccount->balance;

                        foreach ($monthColumnIndices as $monthName => $columnIndex) {
                            $depositAmount = $this->parseNumeric($row[$columnIndex] ?? null);

                            if ($depositAmount && $depositAmount > 0) {
                                $monthNumber = $this->getMonthNumber($monthName);
                                $depositDate = "{$year}-{$monthNumber}-01";

                                $existingTransaction = Transaction::where('account_id', $shareDepositsAccount->id)
                                    ->where('transaction_type', 'deposit')
                                    ->whereYear('processed_at', $year)
                                    ->whereMonth('processed_at', $monthNumber)
                                    ->exists();

                                if (!$existingTransaction) {
                                    $balanceBefore = $currentBalance;
                                    $currentBalance += $depositAmount;

                                    Transaction::create([
                                        'transaction_id' => $this->generateTransactionId(),
                                        'account_id' => $shareDepositsAccount->id,
                                        'member_id' => $member->id,
                                        'transaction_type' => 'deposit',
                                        'amount' => $depositAmount,
                                        'balance_before' => $balanceBefore,
                                        'balance_after' => $currentBalance,
                                        'description' => 'Monthly deposit - '.strtoupper($monthName)." {$year}",
                                        'status' => 'completed',
                                        'processed_by' => auth()->id(),
                                        'processed_at' => $depositDate,
                                        'created_at' => $depositDate,
                                    ]);

                                    $memberDepositsCount++;
                                    $totalDepositsProcessed++;
                                }
                            }
                        }

                        if ($memberDepositsCount > 0) {
                            $shareDepositsAccount->update([
                                'balance' => $currentBalance,
                                'available_balance' => $currentBalance,
                                'last_transaction_at' => now(),
                            ]);
                            $successCount++;
                        } else {
                            $skippedCount++;
                            $errors[] = [
                                'row' => $rowNumber,
                                'name' => $memberName,
                                'errors' => ['No deposits found for any month'],
                            ];
                        }

                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = [
                            'row' => $rowNumber,
                            'name' => $memberName ?? 'Unknown',
                            'errors' => [$e->getMessage()],
                        ];
                    }
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
            }
        }

        $message = "Deposit import completed: {$successCount} members processed, {$totalDepositsProcessed} deposits recorded";
        if ($errorCount > 0) $message .= ", {$errorCount} errors";
        if ($skippedCount > 0) $message .= ", {$skippedCount} skipped";

        return redirect()->route('members.deposits.import.form')
            ->with('success', $message)
            ->with('import_errors', $errors);

    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Failed to process deposit import: '.$e->getMessage()]);
    }
}



/**
 * Improved member name matching
 */
private function findMemberByName(string $searchName): ?Member
{
    // Clean up the name
    $cleanName = trim(preg_replace('/\s+/', ' ', $searchName));
    
    // Strategy 1: Exact match (case-insensitive)
    $member = Member::whereRaw("UPPER(CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name)) = ?", 
        [strtoupper($cleanName)])
        ->first();
    
    if ($member) {
        \Log::debug("Found member using exact match", ['strategy' => 'exact']);
        return $member;
    }
    
    // Strategy 2: Match without middle name/initial
    $member = Member::whereRaw("UPPER(CONCAT(first_name, ' ', last_name)) = ?", 
        [strtoupper($cleanName)])
        ->first();
    
    if ($member) {
        \Log::debug("Found member without middle name", ['strategy' => 'no_middle']);
        return $member;
    }
    
    // Strategy 3: Split name and match parts flexibly
    $nameParts = explode(' ', $cleanName);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[count($nameParts) - 1] ?? '';
    
    // Try matching first and last name only
    if (count($nameParts) >= 2 && $firstName && $lastName) {
        $member = Member::where(function($query) use ($firstName, $lastName) {
            $query->whereRaw("UPPER(first_name) = ?", [strtoupper($firstName)])
                  ->whereRaw("UPPER(last_name) = ?", [strtoupper($lastName)]);
        })->first();
        
        if ($member) {
            \Log::debug("Found member using first and last name", ['strategy' => 'first_last']);
            return $member;
        }
    }
    
    // Strategy 4: Handle middle initials (e.g., "FRANCIS P. MBUQUA")
    if (count($nameParts) == 3 && strlen($nameParts[1]) <= 2) {
        $middleInitial = rtrim($nameParts[1], '.');
        
        $member = Member::where(function($query) use ($firstName, $middleInitial, $lastName) {
            $query->whereRaw("UPPER(first_name) = ?", [strtoupper($firstName)])
                  ->whereRaw("UPPER(last_name) = ?", [strtoupper($lastName)])
                  ->whereRaw("UPPER(LEFT(middle_name, 1)) = ?", [strtoupper($middleInitial)]);
        })->first();
        
        if ($member) {
            \Log::debug("Found member using middle initial", ['strategy' => 'middle_initial']);
            return $member;
        }
    }
    
    // Strategy 5: Fuzzy match with LIKE (more lenient)
    $member = Member::where(function ($query) use ($firstName, $lastName, $cleanName) {
        $query->where(function ($q) use ($firstName, $lastName) {
            $q->where('first_name', 'LIKE', "%{$firstName}%")
              ->where('last_name', 'LIKE', "%{$lastName}%");
        })->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$cleanName}%")
          ->orWhere(DB::raw("CONCAT(first_name, ' ', middle_name, ' ', last_name)"), 'LIKE', "%{$cleanName}%");
    })->first();
    
    if ($member) {
        \Log::debug("Found member using fuzzy match", ['strategy' => 'fuzzy']);
        return $member;
    }
    
    \Log::warning("No member found for name", [
        'search_name' => $cleanName,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'strategies_tried' => 5
    ]);
    
    return null;
}

    /**
     * Show deposit import form
     */
    public function showDepositsImportForm()
    {
        return Inertia::render('Admin/Members/ImportDeposits', [
            'currentYear' => date('Y'),
            'years' => range(2020, 2030),
        ]);
    }

    /**
     * Download deposit import template
     */
    public function downloadDepositsTemplate()
    {
        $headers = [
            'name', 'shares',
            'JAN', 'FEB', 'MAR', 'APRIL', 'MAY', 'JUNE',
            'JULY', 'AUG', 'SEPT', 'OCT', 'NOV', 'DEC',
        ];

        $sampleData = [
            [
                'FRANCIS P. MBUQUA', '5000.00',
                '5000.00', '5000.00', '5000.00', '5000.00', '5000.00', '5000.00',
                '5000.00', '5000.00', '5000.00', '', '', '',
            ],
            [
                'SAMUEL G. MUTUNE', '3000.00',
                '', '', '', '', '', '',
                '', '', '', '', '', '',
            ],
            [
                'EDWARD M. ISINDU', '5000.00',
                '5000.00', '5000.00', '5000.00', '5000.00', '5000.00', '5000.00',
                '', '', '', '', '', '',
            ],
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->fromArray($headers, null, 'A1');

        // Add sample data
        $sheet->fromArray($sampleData, null, 'A2');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0A2342'], // Dark Blue
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

        // Style data rows only (row 2 onward)
        $dataRowStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F5E9'], 
            ],
            'numberFormat' => [
                'formatCode' => '#,##0.00',
            ],
        ];

// Apply only from row 2 downward
$sheet->getStyle('C2:N1000')->applyFromArray($dataRowStyle);


        // Add instructions sheet
        $instructionSheet = $spreadsheet->createSheet();
        $instructionSheet->setTitle('Instructions');

        $instructions = [
            ['MONTHLY DEPOSITS IMPORT INSTRUCTIONS'],
            [''],
            ['This template is for importing monthly deposits for EXISTING members'],
            [''],
            ['Required Columns:'],
            ['- name: Member\'s full name (must match existing member in system)'],
            [''],
            ['Optional Columns:'],
            ['- shares: Initial share capital (if not already recorded)'],
            ['- JAN through DEC: Monthly deposit amounts'],
            [''],
            ['Important Notes:'],
            ['1. Members must already exist in the system'],
            ['2. System will try to match members by name (fuzzy matching)'],
            ['3. Duplicate transactions will be skipped automatically'],
            ['4. Leave month cells empty if no deposit for that month'],
            ['5. You must specify the year when importing'],
            ['6. Share capital will only be added if not already recorded'],
            [''],
            ['Name Matching:'],
            ['- System will try to match: "FRANCIS P. MBUQUA" with "Francis Mbuqua"'],
            ['- First and last names are prioritized in matching'],
            ['- If member not found, that row will be skipped with error'],
            [''],
            ['Example Names from your spreadsheet:'],
            ['- FRANCIS P. MBUQUA'],
            ['- SAMUEL G. MUTUNE'],
            ['- EDWARD M. ISINDU'],
        ];

        $instructionSheet->fromArray($instructions, null, 'A1');
        $instructionSheet->getColumnDimension('A')->setWidth(80);
        $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Auto-size columns
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze header
        $sheet->freezePane('A2');

        $spreadsheet->setActiveSheetIndex(0);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $fileName = 'deposits_import_template_'.date('Y-m-d').'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Get month number from month name
     */
    private function getMonthNumber(string $monthName): string
    {
        $months = [
            'jan' => '01', 'january' => '01',
            'feb' => '02', 'february' => '02',
            'mar' => '03', 'march' => '03',
            'april' => '04', 'apr' => '04',
            'may' => '05',
            'june' => '06', 'jun' => '06',
            'july' => '07', 'jul' => '07',
            'aug' => '08', 'august' => '08',
            'sept' => '09', 'sep' => '09', 'september' => '09',
            'oct' => '10', 'october' => '10',
            'nov' => '11', 'november' => '11',
            'dec' => '12', 'december' => '12',
        ];

        return $months[strtolower($monthName)] ?? '01';
    }
}
