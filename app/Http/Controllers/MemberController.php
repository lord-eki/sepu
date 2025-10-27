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

class MemberController extends Controller
{


    /**
     * Show the import form
     */

    public function showImportForm()
    {
        return  Inertia::render('Admin/Members/Import',[
            'sampleHeaders' => [
            'first_name', 'last_name', 'middle_name', 'date_of_birth', 'gender',
                'marital_status', 'email', 'phone', 'physical_address', 'postal_address',
                'city', 'county', 'id_type', 'id_number', 'occupation', 'employer',
                'monthly_income', 'emergency_contact_name', 'emergency_contact_phone',
                'emergency_contact_relationship'
            ]
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

            // Create user account
            $user = User::create([
                'name' => $request->first_name.' '.$request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password ?? Str::random(12)),
                'role' => 'member',
                'is_active' => true,
            ]);

            // Create member profile
            $memberData = $request->validated();
            $memberData['user_id'] = $user->id;
            $memberData['membership_id'] = $this->generateMembershipId();
            $memberData['membership_date'] = now();
            $memberData['membership_status'] = 'active';

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $memberData['profile_photo'] = $request->file('profile_photo')
                    ->store('members/photos', 'public');
            }

            // Handle documents upload
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

            // Create default accounts (savings and shares)
            $member->accounts()->create([
                'account_number' => $this->generateAccountNumber('SAV'),
                'account_type' => 'savings',
                'balance' => 0,
                'available_balance' => 0,
                'is_active' => true,
            ]);

            $member->accounts()->create([
                'account_number' => $this->generateAccountNumber('SHR'),
                'account_type' => 'shares',
                'balance' => 0,
                'available_balance' => 0,
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()->route('members.index', $member)
                ->with('success', 'Member created successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to create member: '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified member
     */
    public function show(Member $member): Response
    {
        $member->load([
            'user',
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
            'stats' => [
                'total_savings' => $member->accounts->where('account_type', 'savings')->sum('balance'),
                'total_shares' => $member->accounts->where('account_type', 'shares')->sum('balance'),
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

            // Check if member has active loans
            if ($member->loans()->whereIn('status', ['active', 'pending', 'approved'])->exists()) {
                return back()->withErrors(['error' => 'Cannot delete member with active loans']);
            }

            // Check if member has account balances
            if ($member->accounts()->where('balance', '>', 0)->exists()) {
                return back()->withErrors(['error' => 'Cannot delete member with account balances']);
            }

            // Soft delete member (preserves historical data)
            $member->delete();

            // Deactivate user account
            $member->user->update(['is_active' => false]);

            DB::commit();

            return redirect()->route('members.index')
                ->with('success', 'Member deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to delete member: '.$e->getMessage()]);
        }
    }

    /**
     * Activate member
     */
    public function activate(Member $member): RedirectResponse
    {
        $member->update(['membership_status' => 'active']);
        $member->user->update(['is_active' => true]);

        return back()->with('success', 'Member activated successfully');
    }

    /**
     * Deactivate member
     */
    public function deactivate(Member $member): RedirectResponse
    {
        $member->update(['membership_status' => 'inactive']);
        $member->user->update(['is_active' => false]);

        return back()->with('success', 'Member deactivated successfully');
    }

    /**
     * Suspend member
     */
    public function suspend(Member $member): RedirectResponse
    {
        $member->update(['membership_status' => 'suspended']);
        $member->user->update(['is_active' => false]);

        return back()->with('success', 'Member suspended successfully');
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
        $member->nextOfKin()->create($request->validated());

        return back()->with('success', 'Next of kin added successfully');
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
        // Implementation for CSV/Excel export
        return response()->json(['message' => 'Export functionality to be implemented']);
    }

    /**
     * Bulk import members
     */
    public function bulkImport(Request $request): RedirectResponse
    {
            $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Get headers from first row
            $headers = array_map('trim', array_map('strtolower', $rows[0]));
            
            // Remove header row
            array_shift($rows);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; 

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map row data to headers
                $data = array_combine($headers, $row);

                // Validate row data
                $validator = Validator::make($data, [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'middle_name' => 'nullable|string|max:255',
                    'date_of_birth' => 'required|date',
                    'gender' => 'required|in:male,female,other',
                    'marital_status' => 'required|in:single,married,divorced,widowed',
                    'email' => 'required|email|unique:users,email',
                    'phone' => 'required|string|max:20',
                    'physical_address' => 'required|string',
                    'postal_address' => 'required|string',
                    'city' => 'nullable|string|max:255',
                    'county' => 'required|string|max:255',
                    'id_type' => 'required|in:national_id,passport,driving_license',
                    'id_number' => 'required|string|unique:members,id_number',
                    'occupation' => 'nullable|string|max:255',
                    'employer' => 'nullable|string|max:255',
                    'monthly_income' => 'nullable|numeric|min:0',
                    'emergency_contact_name' => 'required|string|max:255',
                    'emergency_contact_phone' => 'required|string|max:20',
                    'emergency_contact_relationship' => 'required|string|max:255',
                ]);

                if ($validator->fails()) {
                    $errorCount++;
                    $errors[] = [
                        'row' => $rowNumber,
                        'name' => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
                        'errors' => $validator->errors()->all()
                    ];
                    continue;
                }

                try {
                    // Create user account
                    $user = User::create([
                        'name' => trim($data['first_name'] . ' ' . $data['last_name']),
                        'email' => trim($data['email']),
                        'phone' => trim($data['phone']),
                        'password' => Hash::make(Str::random(12)), // Random password
                        'role' => 'member',
                        'is_active' => true,
                    ]);

                    // Create member profile
                    $member = Member::create([
                        'user_id' => $user->id,
                        'membership_id' => $this->generateMembershipId(),
                        'first_name' => trim($data['first_name']),
                        'last_name' => trim($data['last_name']),
                        'middle_name' => isset($data['middle_name']) ? trim($data['middle_name']) : null,
                        'date_of_birth' => $data['date_of_birth'],
                        'gender' => $data['gender'],
                        'marital_status' => $data['marital_status'],
                        'physical_address' => trim($data['physical_address']),
                        'postal_address' => trim($data['postal_address']),
                        'city' => isset($data['city']) ? trim($data['city']) : null,
                        'county' => trim($data['county']),
                        'id_type' => $data['id_type'],
                        'id_number' => trim($data['id_number']),
                        'occupation' => isset($data['occupation']) ? trim($data['occupation']) : null,
                        'employer' => isset($data['employer']) ? trim($data['employer']) : null,
                        'monthly_income' => isset($data['monthly_income']) ? $data['monthly_income'] : null,
                        'emergency_contact_name' => trim($data['emergency_contact_name']),
                        'emergency_contact_phone' => trim($data['emergency_contact_phone']),
                        'emergency_contact_relationship' => trim($data['emergency_contact_relationship']),
                        'membership_date' => now(),
                        'membership_status' => 'active',
                    ]);

                    // Create default accounts
                    $member->accounts()->create([
                        'account_number' => $this->generateAccountNumber('SAV'),
                        'account_type' => 'savings',
                        'balance' => 0,
                        'available_balance' => 0,
                        'is_active' => true,
                    ]);

                    $member->accounts()->create([
                        'account_number' => $this->generateAccountNumber('SHR'),
                        'account_type' => 'shares',
                        'balance' => 0,
                        'available_balance' => 0,
                        'is_active' => true,
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = [
                        'row' => $rowNumber,
                        'name' => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
                        'errors' => [$e->getMessage()]
                    ];
                }
            }

            DB::commit();

            return redirect()->route('members.index')->with('success', 
                "Import completed: {$successCount} members imported successfully" . 
                ($errorCount > 0 ? ", {$errorCount} failed." : ".")
            )->with('import_errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process import: ' . $e->getMessage()]);
        }
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
            'emergency_contact_relationship'
        ];

        $sampleData = [
            [
                'John', 'Doe', 'Smith', '1990-01-15', 'male',
                'married', 'john.doe@example.com', '+254712345678', '123 Main St',
                'P.O. Box 123', 'Nairobi', 'Nairobi', 'national_id', '12345678',
                'Teacher', 'ABC School', '50000', 'Jane Doe', '+254712345679',
                'Spouse'
            ]
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
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
                'startColor' => ['rgb' => '0A2342']
            ],
            'font' => ['color' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A1:T1')->applyFromArray($headerStyle);

        // Auto-size columns
        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        
        $fileName = 'members_import_template_' . date('Y-m-d') . '.xlsx';
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
                // Fallback: count all members + 1
                $number = Member::count() + 1;
            }
        }

        return 'SEPU/SACCO/'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique account number
     */
    private function generateAccountNumber(string $prefix): string
    {
        do {
            $number = $prefix.str_pad(random_int(1, 9999999), 7, '0', STR_PAD_LEFT);
        } while (DB::table('accounts')->where('account_number', $number)->exists());

        return $number;
    }
}
