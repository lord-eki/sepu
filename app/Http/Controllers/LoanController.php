<?php

namespace App\Http\Controllers;

use App\Models\{Account, AuditLog, Loan, LoanGuarantor, LoanProduct, LoanRepayment, Member, PaymentVoucher, Transaction};
use App\Services\LoanEligibilityService;
use App\Services\Loans\LoanCalculator;
use App\Services\Loans\LoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LoanController extends Controller
{
    /**
     * Display a listing of loans
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $scopeMemberId = null;

        if ($user->role === 'member') {
            $scopeMemberId = $user->member?->id;
            abort_unless($scopeMemberId, 403);
        }

        $query = Loan::with(['member', 'loanProduct', 'approvedBy', 'disbursedBy'])
            ->when($scopeMemberId, fn($q) => $q->where('member_id', $scopeMemberId));

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('member_id') && $request->member_id !== '') {
            $query->where('member_id', $request->member_id);
        }

        if ($request->has('loan_product_id') && $request->loan_product_id !== '') {
            $query->where('loan_product_id', $request->loan_product_id);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('application_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('application_date', '<=', $request->date_to);
        }


        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($memberQuery) use ($search) {
                        $memberQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('membership_id', 'like', "%{$search}%");
                    });
            });
        }

        $loans = $query->orderBy('application_date', 'desc')->paginate(20);

        return Inertia::render('Shared/Loans/Index', [
            'loans'   => $loans,
            'summary' => $this->getLoansSummary($scopeMemberId),
            'filters' => $request->only(['status', 'search', 'member_id', 'loan_product_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new loan
     */
    public function create()
    {
        $user = auth()->user()->load('member');

        $loanProducts = LoanProduct::where('is_active', true)->get();
        $members = Member::where('membership_status', 'active')
            ->when($user->role === 'member', fn($q) => $q->where('id', $user->member?->id))
            ->get();

        return Inertia::render('Shared/Loans/Create', [
            'loanProducts' => $loanProducts,
            'members'      => $members,
            'auth'         => ['user' => $user],
        ]);
    }

    /**
     * Check loan eligibility for a member
     */
    public function checkEligibility(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'loan_product_id' => 'required|exists:loan_products,id',
            'requested_amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $member = Member::findOrFail($request->member_id);
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

    /**
     * Store a newly created loan application
     */
    public function store(Request $request)
    {

        $user = Auth::user();

        if ($user->role === 'member') {
            abort_unless($user->member, 403, 'No member profile is linked to this account.');
            $request->merge(['member_id' => $user->member->id]);
        }

        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'loan_product_id' => 'required|exists:loan_products,id',
            'applied_amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',

            'guarantors' => 'required|array|min:1',
            'guarantors.*.member_id' => 'required|exists:members,id',
            'guarantors.*.guaranteed_amount' => 'required|numeric|min:1',
            'documents'   => 'sometimes|array|max:10',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $loanProduct = LoanProduct::findOrFail($request->loan_product_id);
            $member = Member::findOrFail($request->member_id);

            $eligibilityService = new LoanEligibilityService;
            $eligibility = $eligibilityService->checkEligibility(
                $member,
                $loanProduct,
                $request->applied_amount
            );

            if (!$eligibility['eligible']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member is not eligible for this loan',
                    'eligibility' => $eligibility,
                ], 422);
            }

            if (
                $request->applied_amount < $loanProduct->min_amount ||
                $request->applied_amount > $loanProduct->max_amount
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan amount outside allowed range.',
                ], 422);
            }

            if (
                $request->term_months < $loanProduct->min_term_months ||
                $request->term_months > $loanProduct->max_term_months
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan term outside allowed range.',
                ], 422);
            }

            $existingLoans = Loan::where('member_id', $request->member_id)
                ->whereIn('status', Loan::OPEN_STATUSES)
                ->count();

            if ($existingLoans > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member has existing active loan applications.',
                ], 422);
            }

            $totalGuaranteed = collect($request->guarantors)
                ->sum('guaranteed_amount');

            if ($totalGuaranteed < $request->applied_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total guarantor amount must equal or exceed requested loan amount.',
                ], 422);
            }

            foreach ($request->guarantors as $guarantorData) {

                if ($guarantorData['member_id'] == $request->member_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Applicant cannot be their own guarantor.',
                    ], 422);
                }

                $guarantor = Member::findOrFail($guarantorData['member_id']);

                $depositBalance = $guarantor->accounts()
                    ->where('account_type', 'share_deposits')
                    ->sum('available_balance');

                if ($depositBalance < $guarantorData['guaranteed_amount']) {
                    return response()->json([
                        'success' => false,
                        'message' => $guarantor->first_name . ' has insufficient deposits to guarantee requested amount.',
                    ], 422);
                }
            }


            $calc = app(LoanCalculator::class)->summary(
                (float) $request->applied_amount,
                (float) $loanProduct->interest_rate,
                (int) $request->term_months,
                (float) $loanProduct->processing_fee_rate,
                (float) $loanProduct->insurance_rate,
            );

            $processingFee    = $calc['processing_fee'];
            $insuranceFee     = $calc['insurance_fee'];
            $monthlyRepayment = $calc['monthly_payment'];
            $totalRepayable   = $calc['total_repayable'];

            $documents = [];

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $index => $file) {
                    $filename = Str::uuid() . '.' . $file->extension();
                    $path = $file->storeAs('loan-documents', $filename, 'local');

                    $documents[] = [
                        'type' => $request->document_types[$index] ?? 'Document',
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'disk' => 'local',
                    ];
                }
            }

            $loan = Loan::create([
                'loan_number' => $this->generateLoanNumber(),
                'member_id' => $request->member_id,
                'loan_product_id' => $request->loan_product_id,
                'applied_amount' => $request->applied_amount,
                'interest_rate' => $loanProduct->interest_rate,
                'term_months' => $request->term_months,
                'monthly_repayment' => $monthlyRepayment,
                'total_repayable' => $totalRepayable,
                'processing_fee' => $processingFee,
                'insurance_fee' => $insuranceFee,
                'purpose' => $request->purpose,
                'status' => 'pending_guarantor_approval',
                'application_date' => now(),
                'documents' => $documents,
                'outstanding_balance' => 0,
                'principal_balance' => 0,
                'interest_balance' => 0,
                'penalty_balance' => 0,
                'days_in_arrears' => 0,
            ]);


            $notifications = [];

            foreach ($request->guarantors as $guarantorData) {

                $guarantorMember = Member::with('user')->findOrFail($guarantorData['member_id']);

                LoanGuarantor::create([
                    'loan_id' => $loan->id,
                    'guarantor_member_id' => $guarantorData['member_id'],
                    'guaranteed_amount' => $guarantorData['guaranteed_amount'],
                    'status' => 'pending',
                ]);

                if ($guarantorMember->user) {

                    app(NotificationService::class)->create(
                        $guarantorMember->user->id,
                        'Guarantor Request',
                        'You have been requested to guarantee a loan of KES ' . number_format($guarantorData['guaranteed_amount']),
                        'guarantor_request',
                        'system'
                    )->update([
                        'metadata' => [
                            'loan_id' => $loan->id,
                            'loan_number' => $loan->loan_number,
                            'guaranteed_amount' => $guarantorData['guaranteed_amount'],
                            'borrower' => $member->first_name . ' ' . $member->last_name,
                        ]
                    ]);
                }
            }
            /**
             * AUDIT LOG
             */
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'loan_application_created',
                'model_type' => 'App\Models\Loan',
                'model_id' => $loan->id,
                'new_values' => $loan->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan submitted. Waiting for guarantor approvals.',
                'data' => $loan->load([
                    'member',
                    'loanProduct',
                    'guarantors.guarantorMember'
                ]),
            ]);
        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error in creating loan application: ' . \App\Support\ErrorMessage::from($e),
            ], 500);
        }
    }

    /**
     * Display the specified loan
     */
    public function show($id)
    {
        $loan = Loan::with([
            'member.user',
            'loanProduct',
            'guarantors.guarantorMember',
            'repayments',
            'approvedBy',
            'disbursedBy',
        ])->findOrFail($id);

        Gate::authorize('view', $loan);

        return Inertia::render('Shared/Loans/Show', [
            'loan' => $loan,
        ]);
    }

    /**
     * Show loan repayments
     */
    public function repayments($id)
    {
        $loan = Loan::with(['member.user', 'loanProduct'])->findOrFail($id);

        Gate::authorize('view', $loan);

        $repayments = $this->scheduleFor($loan);

        return Inertia::render('Shared/Loans/Repayment', [
            'loan' => $loan,
            'repayments' => $repayments,
        ]);
    }

    /**
     * Show the form for editing the specified loan
     */
    public function edit($id)
    {
        $loan = Loan::with(['member', 'loanProduct', 'guarantors.guarantorMember'])->findOrFail($id);

        Gate::authorize('view', $loan);

        if ($loan->status !== 'pending') {
            return redirect()->route('loans.show', $loan->id)
                ->with('error', 'Only pending loans can be edited');
        }

        $loanProducts = LoanProduct::where('is_active', true)->get();
        $members = Member::where('membership_status', 'active')->get();

        return Inertia::render('Shared/Loans/Edit', [
            'loan' => $loan,
            'loanProducts' => $loanProducts,
            'members' => $members,
        ]);
    }

    /**
     * Update the specified loan
     */
    public function update(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending loans can be updated',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'applied_amount' => 'sometimes|numeric|min:1',
            'term_months' => 'sometimes|integer|min:1',
            'purpose' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldValues = $loan->toArray();


            $principal  = $request->applied_amount ?? $loan->applied_amount;
            $termMonths = $request->term_months ?? $loan->term_months;

            $loan->update([
                'applied_amount' => $principal,
                'term_months' => $termMonths,
                'purpose' => $request->purpose,
            ]);
            $loan->load('loanProduct');
            $loanProduct = $loan->loanProduct;

            $processingFee = ($principal * $loanProduct->processing_fee_rate) / 100;
            $insuranceFee  = ($principal * $loanProduct->insurance_fee_rate) / 100;
            $monthlyRate = $loanProduct->interest_rate / 100;
            $totalInterest = $principal * $monthlyRate * ($termMonths + 1) / 2;
            $principalPerMonth = $principal / $termMonths;
            $monthlyInterest = $totalInterest / $termMonths;

            $monthlyRepayment = $principalPerMonth + $monthlyInterest;
            $totalRepayable   = $monthlyRepayment * $termMonths;

            $loan->update([
                'monthly_repayment'   => round($monthlyRepayment, 2),
                'total_repayable'     => round($totalRepayable, 2),
                'processing_fee'      => round($processingFee, 2),
                'insurance_fee'       => round($insuranceFee, 2),

                'outstanding_balance' => round($totalRepayable, 2),
                'principal_balance'   => round($principal, 2),
                'interest_balance'    => round($totalInterest, 2),
            ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'loan_updated',
                'model_type' => 'App\Models\Loan',
                'model_id' => $loan->id,
                'old_values' => $oldValues,
                'new_values' => $loan->fresh()->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan updated successfully',
                'data' => $loan->load(['member', 'loanProduct']),
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error updating loan: ' . \App\Support\ErrorMessage::from($e),
            ], 500);
        }
    }


    /**
     * Approve a loan application
     */
    public function approve(Request $request, $id, LoanService $loans)
    {
        $validator = Validator::make($request->all(), [
            'approved_amount' => 'required|numeric|min:1',
            'approval_notes'  => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed, check all fields',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $loan = $loans->approve((int) $id, (float) $request->approved_amount, Auth::user(), $request->approval_notes);

            return response()->json([
                'success' => true,
                'message' => 'Loan approved successfully',
                'data'    => $loan->load(['member', 'loanProduct']),
            ]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Error approving loan. Please try again or contact support.'], 500);
        }
    }

    /**
     * Reject a loan application
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $loan = Loan::findOrFail($id);

        if (! in_array($loan->status, ['pending', 'approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or approved loans can be rejected',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldValues = $loan->toArray();

            $loan->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'approved_by' => Auth::id(),
                'approval_date' => now(),
            ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'loan_rejected',
                'model_type' => 'App\Models\Loan',
                'model_id' => $loan->id,
                'old_values' => $oldValues,
                'new_values' => $loan->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan rejected successfully',
                'data' => $loan->load(['member', 'loanProduct']),
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error rejecting loan: ' . \App\Support\ErrorMessage::from($e),
            ], 500);
        }
    }


    /**
     * Disburse an approved loan
     */
    public function disburse(Request $request, $id, LoanService $loans)
    {
        $validator = Validator::make($request->all(), [
            'disbursement_method'    => 'required|in:cash,mobile_money,bank_transfer,account',
            'disbursement_reference' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = $loans->disburse(
                (int) $id,
                $request->disbursement_method,
                $request->disbursement_reference,
                Auth::user(),
            );

            return response()->json([
                'success' => true,
                'message' => 'Loan disbursed successfully',
                'data'    => [
                    'loan'             => $result['loan']->load(['member', 'loanProduct']),
                    'transaction'      => $result['transaction'],
                    'net_disbursement' => $result['net_disbursement'],
                ],
            ]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Error disbursing loan. Please try again or contact support.'], 500);
        }
    }
    /**
     * Get loan statistics and summary
     */
    public function getLoansSummary(?int $memberId = null)
    {
        $loans = fn() => Loan::query()->when($memberId, fn($q) => $q->where('member_id', $memberId));

        return [
            'total_loans'               => $loans()->count(),
            'pending_loans'             => $loans()->where('status', 'pending')->count(),
            'approved_loans'            => $loans()->where('status', 'approved')->count(),
            'disbursed_loans'           => $loans()->whereIn('status', Loan::RUNNING_STATUSES)->count(),
            'total_outstanding_balance' => $loans()->whereIn('status', Loan::RUNNING_STATUSES)->sum('outstanding_balance'),
            'completed_loans'           => $loans()->where('status', 'completed')->count(),
            'total_applied_amount'      => $loans()->sum('applied_amount'),
            'total_approved_amount'     => $loans()->whereNotNull('approved_amount')->sum('approved_amount'),
            'total_disbursed_amount'    => $loans()->whereNotNull('disbursed_amount')->sum('disbursed_amount'),
            'overdue_loans'             => $loans()->where('days_in_arrears', '>', 0)->count(),
            'this_month_applications'   => $loans()->whereMonth('application_date', now()->month)->count(),
            'this_month_disbursements'  => $loans()->whereMonth('disbursement_date', now()->month)->count(),
        ];
    }


    /**
     * Generate unique loan number
     */
    private function generateLoanNumber()
    {
        $prefix = 'LN';
        $year = date('Y');
        $month = date('m');

        $lastLoan = Loan::where('loan_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('loan_number', 'desc')
            ->first();

        if ($lastLoan) {
            $lastNumber = intval(substr($lastLoan->loan_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique account number
     */
    private function generateAccountNumber()
    {
        do {
            $accountNumber = '01' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Account::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    /**
     * Generate unique transaction ID
     */
    private function generateTransactionId()
    {
        do {
            $transactionId = 'TXN' . date('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        } while (Transaction::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }

    /**
     * Generate unique voucher number
     */
    private function generateVoucherNumber()
    {
        do {
            $voucherNumber = 'VOU' . date('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        } while (PaymentVoucher::where('voucher_number', $voucherNumber)->exists());

        return $voucherNumber;
    }


    /**
     * Loan Schedule
     */
    public function schedule($id)
    {
        $loan = Loan::with(['member', 'loanProduct', 'repayments'])->findOrFail($id);

        if ($loan->repayments->isNotEmpty()) {

            $openingBalance = (float) $loan->approved_amount;

            $repayments = $loan->repayments
                ->sortBy('due_date')
                ->values()
                ->map(function ($r, $i) use (&$openingBalance) {

                    $principal = (float) $r->principal_amount;
                    $interest  = (float) $r->interest_amount;

                    $expected = (float) ($r->expected_amount ?? ($principal + $interest));

                    $closing = $openingBalance - $principal;

                    $data = [
                        'payment_number'   => $i + 1,

                        'payment_date'     => $r->payment_date
                            ?? $r->due_date
                            ?? null,

                        'opening_balance'  => round($openingBalance, 2),
                        'principal_amount' => round($principal, 2),
                        'interest_amount'  => round($interest, 2),
                        'payment_amount'   => round($expected, 2),
                        'closing_balance'  => round(max(0, $closing), 2),
                        'expected_amount'  => $r->expected_amount,
                        'status'           => $r->status,
                    ];

                    $openingBalance = $closing;

                    return $data;
                });
        } else {

            $repayments = $this->scheduleFor($loan);
        }

        $totals = [
            'principal' => round($repayments->sum('principal_amount'), 2),
            'interest'  => round($repayments->sum('interest_amount'), 2),
            'total'     => round($repayments->sum('payment_amount'), 2),
        ];

        return Inertia::render('Shared/Loans/Schedule', [
            'loan'       => $loan,
            'repayments' => $repayments,
            'totals'     => $totals,
            'message'    => $repayments->isEmpty()
                ? 'No repayment schedule generated for this loan yet.'
                : null,
        ]);
    }


    public function myGuarantees()
    {
        $member = Auth::user()->member;

        $guarantees = LoanGuarantor::with('loan.member', 'loan.loanProduct')
            ->where('guarantor_member_id', $member->id)
            ->latest()
            ->get();

        return Inertia::render('Shared/Loans/MyGuarantees', [
            'guarantees' => $guarantees
        ]);
    }

    public function guarantorRequestPage(Loan $loan)
    {
        $user = Auth::user();

        $loan->load(['member', 'guarantors.guarantorMember']);

        $guarantor = LoanGuarantor::with('guarantorMember')
            ->where('loan_id', $loan->id)
            ->whereHas('guarantorMember', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->first();

        if (!$guarantor) {
            abort(403, 'You are not a guarantor for this loan');
        }

        return Inertia::render('Shared/Loans/GuarantorRequest', [
            'loan' => $loan,
            'guarantor' => $guarantor,
        ]);
    }



    public function allGuarantees(Request $request)
    {
        $query = LoanGuarantor::with([
            'loan.member',
            'guarantorMember'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('loan', function ($q2) use ($search) {
                    $q2->where('loan_number', 'like', "%{$search}%")
                        ->orWhereHas('member', function ($q3) use ($search) {
                            $q3->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                })
                    ->orWhereHas('guarantorMember', function ($q4) use ($search) {
                        $q4->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        return Inertia::render('Shared/Loans/Guarantors', [
            'guarantees' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['status', 'search']),
        ]);
    }


    public function acceptGuarantee(Loan $loan)
    {
        $member = Auth::user()->member;

        $guarantor = LoanGuarantor::where('loan_id', $loan->id)
            ->where('guarantor_member_id', $member->id)
            ->firstOrFail();

        $guarantor->update([
            'status' => 'accepted',
            'response_date' => now(),
        ]);

        $pending = LoanGuarantor::where('loan_id', $loan->id)
            ->where('status', 'pending')
            ->count();

        if ($pending == 0) {
            $loan->update([
                'status' => 'pending'
            ]);
        }

        return redirect()->back()->with('success', 'Loan guarantee accepted successfully.');
    }

    public function rejectGuarantee(Loan $loan)
    {
        $member = Auth::user()->member;

        $guarantor = LoanGuarantor::where('loan_id', $loan->id)
            ->where('guarantor_member_id', $member->id)
            ->firstOrFail();

        $guarantor->update([
            'status' => 'rejected',
            'response_date' => now(),
        ]);

        $loan->update([
            'status' => 'guarantor_rejected'
        ]);

        return redirect()->back()->with('error', 'You rejected this guarantor request.');
    }

    public function document(Request $request, Loan $loan, int $index)
    {
        Gate::authorize('view', $loan);

        $doc = $loan->documents[$index] ?? null;
        abort_unless($doc && !empty($doc['path']), 404);

        $path = $doc['path'];

        abort_if(!str_starts_with($path, 'loan-documents/') || str_contains($path, '..'), 404);

        $disk = Storage::disk('local')->exists($path) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($path), 404);

        $name = $doc['name'] ?? basename($path);

        return $request->boolean('download')
            ? Storage::disk($disk)->download($path, $name)
            : Storage::disk($disk)->response($path, $name);
    }

    // Stored schedule once disbursed; a calculator preview before that. 
    private function scheduleFor(Loan $loan): Collection
    {
        $stored = LoanRepayment::where('loan_id', $loan->id)->orderBy('due_date')->get();

        if ($stored->isNotEmpty()) {
            $opening = (float) $loan->approved_amount;

            return $stored->values()->map(function ($r, $i) use (&$opening) {
                $principal = (float) $r->principal_amount;

                $row = [
                    'payment_number'   => $i + 1,
                    'payment_date'     => Carbon::parse($r->due_date)->toDateString(),
                    'opening_balance'  => round($opening, 2),
                    'principal_amount' => round($principal, 2),
                    'interest_amount'  => round((float) $r->interest_amount, 2),
                    'payment_amount'   => round((float) $r->expected_amount, 2),
                    'closing_balance'  => round(max(0, $opening - $principal), 2),
                    'expected_amount'  => $r->expected_amount,
                    'paid_amount'      => $r->paid_amount,
                    'status'           => $r->status,
                ];

                $opening -= $principal;

                return $row;
            });
        }

        $principal = (float) ($loan->approved_amount ?: $loan->applied_amount);

        return collect(app(LoanCalculator::class)->schedule(
            $principal,
            (float) $loan->interest_rate,
            (int) $loan->term_months,
            now()->addMonthNoOverflow()->toDateString(),
        ));
    }
}
