<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Loan;
use App\Models\LoanGuarantor;
use App\Models\LoanProduct;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\PaymentVoucher;
use App\Models\Transaction;
use App\Services\LoanEligibilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GuarantorRequestNotification;
use App\Services\NotificationService;
use Inertia\Inertia;

class LoanController extends Controller
{
    /**
     * Display a listing of loans
     */
    public function index(Request $request)
    {
        $query = Loan::with(['member', 'loanProduct', 'approvedBy', 'disbursedBy']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by member
        if ($request->has('member_id') && $request->member_id !== '') {
            $query->where('member_id', $request->member_id);
        }

        // Filter by loan product
        if ($request->has('loan_product_id') && $request->loan_product_id !== '') {
            $query->where('loan_product_id', $request->loan_product_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('application_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('application_date', '<=', $request->date_to);
        }

        // Search by loan number or member name
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
            'loans' => $loans,
            'summary' => $this->getLoansSummary(),
            'filters' => $request->only(['status', 'search', 'member_id', 'loan_product_id', 'date_from', 'date_to']),
        ]);
    }

   /**
     * Show the form for creating a new loan
     */
    public function create()
    {
        $loanProducts = LoanProduct::where('is_active', true)->get();
        $members = Member::where('membership_status', 'active')->get();

        return Inertia::render('Shared/Loans/Create', [
            'loanProducts' => $loanProducts,
            'members' => $members,
            'auth' => [
                'user' => auth()->user()->load('member'),
            ],
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
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'loan_product_id' => 'required|exists:loan_products,id',
            'applied_amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',

            'guarantors' => 'required|array|min:1',
            'guarantors.*.member_id' => 'required|exists:members,id',
            'guarantors.*.guaranteed_amount' => 'required|numeric|min:1',

            'documents' => 'sometimes|array',
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

            /**
             * Validate loan amount
             */
            if (
                $request->applied_amount < $loanProduct->min_amount ||
                $request->applied_amount > $loanProduct->max_amount
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan amount outside allowed range.',
                ], 422);
            }

            /**
             * Validate term
             */
            if (
                $request->term_months < $loanProduct->min_term_months ||
                $request->term_months > $loanProduct->max_term_months
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan term outside allowed range.',
                ], 422);
            }

            /**
             * Check existing active loans
             */
            $existingLoans = Loan::where('member_id', $request->member_id)
                ->whereIn('status', [
                    'pending_guarantor_approval',
                    'pending',
                    'approved',
                    'disbursed'
                ])
                ->count();

            if ($existingLoans > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member has existing active loan applications.',
                ], 422);
            }

            /**
             * GUARANTOR VALIDATIONS
             */

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

            /**
             * Loan calculations
             */
            $processingFee = ($request->applied_amount * $loanProduct->processing_fee_rate) / 100;
            $insuranceFee = ($request->applied_amount * $loanProduct->insurance_rate) / 100;

            $monthlyInterestRate = $loanProduct->interest_rate / 100 / 12;

            $monthlyRepayment = $this->calculateMonthlyRepayment(
                $request->applied_amount,
                $monthlyInterestRate,
                $request->term_months
            );

            $totalRepayable = $monthlyRepayment * $request->term_months;

            /**
             * CREATE LOAN
             */
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

                // IMPORTANT
                'status' => 'pending_guarantor_approval',

                'application_date' => now(),
                'documents' => $request->documents ?? [],

                'outstanding_balance' => 0,
                'principal_balance' => 0,
                'interest_balance' => 0,
                'penalty_balance' => 0,
                'days_in_arrears' => 0,
            ]);

            /**
             * SAVE GUARANTORS
             */

            $notifications = [];

            foreach ($request->guarantors as $guarantorData) {

            $guarantorMember = Member::with('user')->findOrFail($guarantorData['member_id']);

            LoanGuarantor::create([
                'loan_id' => $loan->id,
                'guarantor_member_id' => $guarantorData['member_id'],
                'guaranteed_amount' => $guarantorData['guaranteed_amount'],
                'status' => 'pending',
            ]);

            // notify EACH guarantor individually
            if ($guarantorMember->user) {

                app(NotificationService::class)->create(
                    $guarantorMember->user->id, // ✅ correct user
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
                'message' => 'Error creating loan application: ' . $e->getMessage(),
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

        $repayments = collect($this->generateRepaymentSchedule($loan));

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
            'documents' => 'sometimes|array',
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

            // -----------------------------------
            // SAFE SOURCE VALUES
            // -----------------------------------
            $principal  = $request->applied_amount ?? $loan->applied_amount;
            $termMonths = $request->term_months ?? $loan->term_months;

            $loan->update([
                'applied_amount' => $principal,
                'term_months' => $termMonths,
                'purpose' => $request->purpose,
                'documents' => $request->documents,
            ]);

            // -----------------------------------
            // RELOAD RELATION
            // -----------------------------------
            $loan->load('loanProduct');

            // -----------------------------------
            // PRODUCT DATA
            // -----------------------------------
            $loanProduct = $loan->loanProduct;

            $processingFee = ($principal * $loanProduct->processing_fee_rate) / 100;
            $insuranceFee  = ($principal * $loanProduct->insurance_fee_rate) / 100;

            $monthlyRate = $loanProduct->interest_rate / 100; // MONTHLY RATE (NO /12)

            // -----------------------------------
            // INTEREST + REPAYMENT
            // -----------------------------------
            $totalInterest = $principal * $monthlyRate * ($termMonths + 1) / 2;

            $principalPerMonth = $principal / $termMonths;
            $monthlyInterest = $totalInterest / $termMonths;

            $monthlyRepayment = $principalPerMonth + $monthlyInterest;
            $totalRepayable   = $monthlyRepayment * $termMonths;

            // -----------------------------------
            // UPDATE LOAN FINANCIALS
            // -----------------------------------
            $loan->update([
                'monthly_repayment'   => round($monthlyRepayment, 2),
                'total_repayable'     => round($totalRepayable, 2),
                'processing_fee'      => round($processingFee, 2),
                'insurance_fee'       => round($insuranceFee, 2),

                'outstanding_balance' => round($totalRepayable, 2),
                'principal_balance'   => round($principal, 2),
                'interest_balance'    => round($totalInterest, 2),
            ]);

            // -----------------------------------
            // AUDIT LOG
            // -----------------------------------
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
                'message' => 'Error updating loan: ' . $e->getMessage(),
            ], 500);
        }
    }


   /**
 * Approve a loan application
 */
public function approve(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'approved_amount' => 'required|numeric|min:1'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed, check all fields',
            'errors' => $validator->errors(),
        ], 422);
    }

    $loan = Loan::with('loanProduct')->findOrFail($id);

    if ($loan->status !== 'pending') {
        return response()->json([
            'success' => false,
            'message' => 'Only pending loans can be approved',
        ], 422);
    }

    try {
        DB::beginTransaction();

        $oldValues = $loan->toArray();

        // -------------------------------
        // STEP 1: Update approval details
        // -------------------------------

        // -------------------------------
        // STEP 2: Recalculate using SAME logic as calculator
        // -------------------------------
        $loanProduct = $loan->loanProduct;

        $principal   = $request->approved_amount;
        $termMonths  = $loan->term_months;

        // Rates (DO NOT divide by 12 — already monthly)
        $monthlyRate     = $loanProduct->interest_rate / 100;
        $processingRate  = $loanProduct->processing_fee_rate / 100;
        $insuranceRate   = $loanProduct->insurance_rate / 100;

        // Fees
        $processingFee = $principal * $processingRate;
        $insuranceFee  = $principal * $insuranceRate;
        $totalFees     = $processingFee + $insuranceFee;

        // Principal split
        $principalPerMonth = $principal / $termMonths;

        // SAME interest formula as calculator
        $totalInterest = $principal * $monthlyRate * ($termMonths + 1) / 2;

        // Monthly interest (fixed)
        $mInterest = $totalInterest / $termMonths;

        // Monthly repayment (constant)
        $monthlyRepayment = $principalPerMonth + $mInterest;

        // Totals
        $totalRepayable = $monthlyRepayment * $termMonths;

        // Net disbursement
        $netDisbursement = $principal - $totalFees;

        // -------------------------------
        // STEP 3: Update financial fields
        // -------------------------------
        $loan->update([
            'approved_amount' => $request->approved_amount,
            'status'          => 'approved',
            'approval_date'   => now(),
            'approved_by'     => Auth::id(),
            'approval_notes'  => $request->approval_notes,
            'monthly_repayment'   => round($monthlyRepayment, 2),
            'total_repayable'     => round($totalRepayable, 2),
            'processing_fee'      => round($processingFee, 2),
            'insurance_fee'       => round($insuranceFee, 2),
            'net_disbursement'    => round($netDisbursement, 2),
        ]);

        // -------------------------------
        // STEP 4: Audit Log
        // -------------------------------
        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'loan_approved',
            'model_type' => 'App\Models\Loan',
            'model_id'   => $loan->id,
            'old_values' => $oldValues,
            'new_values' => $loan->fresh()->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Loan approved successfully',
            'data'    => $loan->load(['member', 'loanProduct']),
        ]);

    } catch (\Exception $e) {
        DB::rollback();

        return response()->json([
            'success' => false,
            'message' => 'Error approving loan: ' . $e->getMessage(),
        ], 500);
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

            // Log the activity
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
                'message' => 'Error rejecting loan: '.$e->getMessage(),
            ], 500);
        }
    }

    
/**
 * Disburse an approved loan
 */
public function disburse(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'disbursement_method'    => 'required|in:cash,mobile_money,bank_transfer',
        'disbursement_reference'=> 'sometimes|string|max:100',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $loan = Loan::with(['member.user', 'loanProduct'])->findOrFail($id);

    if ($loan->status === 'disbursed') {
        return response()->json([
            'success' => false,
            'message' => 'Loan already disbursed',
        ], 422);
    }

    if ($loan->status !== 'approved') {
        return response()->json([
            'success' => false,
            'message' => 'Only approved loans can be disbursed',
        ], 422);
    }

    try {
        DB::beginTransaction();

        $oldValues = $loan->toArray();
        $member    = $loan->member;

        // -----------------------------------
        // USE VALUES FROM APPROVAL
        // -----------------------------------
        $grossAmount     = $loan->approved_amount;
        $principal = $loan->approved_amount;

        $processingFee = $principal * ($loan->loanProduct->processing_fee_rate / 100);
        $insuranceFee   = $principal * ($loan->loanProduct->insurance_rate / 100);

        $netDisbursement = $principal - ($processingFee + $insuranceFee);

        if ($netDisbursement <= 0) {
            throw new \Exception('Net disbursement cannot be zero or negative.');
        }

        // -----------------------------------
        // Member account (reference only)
        // -----------------------------------
        $savingsAccount = Account::where('member_id', $member->id)
            ->where('account_type', 'share_deposits')
            ->first();

        // -----------------------------------
        // Create transaction
        // -----------------------------------
        $transaction = Transaction::create([
            'transaction_id'     => $this->generateTransactionId(),
            'account_id'         => $savingsAccount?->id,
            'member_id'          => $member->id,
            'transaction_type'   => 'loan_disbursement',
            'amount'             => $netDisbursement,
            'balance_before'     => $savingsAccount?->balance ?? 0,
            'balance_after'      => $savingsAccount?->balance ?? 0, // unchanged
            'description'        => "Loan disbursement for loan {$loan->loan_number}",
            'reference_number'   => $request->disbursement_reference,
            'payment_method'     => $request->disbursement_method,
            'payment_reference'  => $request->disbursement_reference,
            'status'             => 'completed',
            'processed_by'       => Auth::id(),
            'processed_at'       => now(),
            'metadata' => [
                'loan_id'        => $loan->id,
                'loan_number'    => $loan->loan_number,
                'gross_amount'   => $grossAmount,
                'processing_fee' => $processingFee,
                'insurance_fee'  => $insuranceFee,
                'net_amount'     => $netDisbursement,
            ],
        ]);

        // -----------------------------------
        // Update loan 
        // -----------------------------------
        $disbursementDate = now();
        $loan->update([
            'disbursed_amount'    => $netDisbursement,
            'status'              => 'disbursed',
            'disbursement_date'   => now(),
            'disbursed_by'        => Auth::id(),

            // Dates

            'first_repayment_date'=> $disbursementDate->copy()->addMonthsNoOverflow(1),
            'maturity_date'       => $disbursementDate->copy()->addMonthsNoOverflow($loan->term_months),
            'disbursement_date'   => $disbursementDate,

            'outstanding_balance' => $loan->total_repayable,
            'principal_balance'   => $loan->approved_amount,
            'interest_balance'    => $loan->interest_balance,
        ]);

       // -----------------------------------
        // Generate + STORE repayment schedule
        // -----------------------------------
        LoanRepayment::where('loan_id', $loan->id)->delete(); // safety reset

        $schedule = $this->generateRepaymentSchedule(
                $loan,
                $disbursementDate
            );
        foreach ($schedule as $row) {

            $expected = round($row['payment_amount'], 2);

            LoanRepayment::create([
                'loan_id'             => $loan->id,
                'transaction_id'      => null, // not paid yet
                'due_date'            => $row['payment_date'],

                'expected_amount'     => $expected,
                'principal_amount'    => round($row['principal_amount'], 2),
                'interest_amount'     => round($row['interest_amount'], 2),

                // DEFAULTS FOR NEW LOAN
                'penalty_amount'      => 0,
                'paid_amount'         => 0,
                'outstanding_amount'  => $expected,

                'status'              => 'pending',
                'payment_date'        => null,
                'days_late'           => 0,
            ]);
        }

        // -----------------------------------
        // Payment voucher
        // -----------------------------------
        PaymentVoucher::create([
            'voucher_number' => $this->generateVoucherNumber(),
            'voucher_type'   => 'loan_disbursement',
            'payee_name'     => $member->first_name.' '.$member->last_name,
            'payee_phone'    => $member->user->phone,
            'amount'         => $netDisbursement, // FIXED (not gross)
            'purpose'        => 'Loan disbursement',
            'description'    => "Disbursement for loan {$loan->loan_number}",
            'loan_id'        => $loan->id,
            'status'         => 'paid',
            'created_by'     => Auth::id(),
            'approved_by'    => Auth::id(),
            'paid_by'        => Auth::id(),
            'approval_date'  => now(),
            'payment_date'   => now(),
        ]);

        // -----------------------------------
        // Audit log
        // -----------------------------------
        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'loan_disbursed',
            'model_type' => 'App\Models\Loan',
            'model_id'   => $loan->id,
            'old_values' => $oldValues,
            'new_values' => $loan->fresh()->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Loan disbursed successfully',
            'data' => [
                'loan'             => $loan->load(['member', 'loanProduct']),
                'transaction'      => $transaction,
                'net_disbursement' => $netDisbursement,
            ],
        ]);

    } catch (\Exception $e) {
        DB::rollback();

        return response()->json([
            'success' => false,
            'message' => 'Error disbursing loan: '.$e->getMessage(),
        ], 500);
    }
}
    /**
     * Get loan statistics and summary
     */
    public function getLoansSummary()
    {
        $summary = [
            'total_loans' => Loan::count(),
            'pending_loans' => Loan::where('status', 'pending')->count(),
            'approved_loans' => Loan::where('status', 'approved')->count(),
            'disbursed_loans' => Loan::where('status', 'disbursed')->count(),
            'completed_loans' => Loan::where('status', 'completed')->count(),
            'total_applied_amount' => Loan::sum('applied_amount'),
            'total_approved_amount' => Loan::whereNotNull('approved_amount')->sum('approved_amount'),
            'total_disbursed_amount' => Loan::whereNotNull('disbursed_amount')->sum('disbursed_amount'),
            'total_outstanding_balance' => Loan::whereIn('status', ['disbursed', 'active'])->sum('outstanding_balance'),
            'overdue_loans' => Loan::where('days_in_arrears', '>', 0)->count(),
            'this_month_applications' => Loan::whereMonth('application_date', now()->month)->count(),
            'this_month_disbursements' => Loan::whereMonth('disbursement_date', now()->month)->count(),
        ];

        return $summary;
    }


    

    /**
     * Calculate monthly repayment using PMT formula
     */
    private function calculateMonthlyRepayment($principal, $monthlyRate, $months)
    {
        if ($monthlyRate == 0) {
            return $principal / $months;
        }

        return $principal * ($monthlyRate * pow(1 + $monthlyRate, $months)) / (pow(1 + $monthlyRate, $months) - 1);
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

        return $prefix.$year.$month.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique account number
     */
    private function generateAccountNumber()
    {
        do {
            $accountNumber = '01'.str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Account::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    /**
     * Generate unique transaction ID
     */
    private function generateTransactionId()
    {
        do {
            $transactionId = 'TXN'.date('YmdHis').str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        } while (Transaction::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }

    /**
     * Generate unique voucher number
     */
    private function generateVoucherNumber()
    {
        do {
            $voucherNumber = 'VOU'.date('YmdHis').str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
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

                    // FIX: ensure consistent date fallback logic
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

        // IMPORTANT: align with calculator logic (includes grace + correct date rule)
        $loanProduct = $loan->loanProduct;

        $principal   = (float) $loan->approved_amount;
        $termMonths  = (int) $loan->term_months;
        $monthlyRate = (float) $loan->interest_rate / 100;
        $graceDays   = $loanProduct->grace_period_days ?? 0;

        $principalPerMonth = $principal / $termMonths;

        $totalInterest = $principal * $monthlyRate * ($termMonths + 1) / 2;
        $mInterest     = $totalInterest / $termMonths;

        $actualInstallment = $principalPerMonth + $mInterest;

        $repayments = collect(
            $this->generateSchedule(
                $principal,
                $principalPerMonth,
                $monthlyRate,
                $mInterest,
                $actualInstallment,
                $termMonths,
                $graceDays
            )
        );
    }

    // totals for BOTH cases
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

private function generateRepaymentSchedule(Loan $loan, $baseDate = null): array
{
    $principal   = (float) $loan->approved_amount;
    $termMonths  = (int) $loan->term_months;
    $monthlyRate = (float) $loan->interest_rate / 100;

    // MATCH CALCULATOR LOGIC
    $principalPerMonth = round($principal / $termMonths, 2);

    $totalInterest = $principal * $monthlyRate * ($termMonths + 1) / 2;
    $mInterest     = round($totalInterest / $termMonths, 2);

    $actualInstallment = round($principalPerMonth + $mInterest, 2);

    $openingBalance = $principal;
    $schedule = [];

    /**
     * FIX DATE:
     * Always start next month + force 1st of month logic
     */
    $graceDays = $loan->loanProduct->grace_period_days ?? 0;

    $currentDate = ($baseDate ?? now())
        ->copy()
        ->addDays($graceDays)
        ->addMonthsNoOverflow(1);

    $cumulInterest = 0;
    $cumulPrincipal = 0;

    for ($i = 1; $i <= $termMonths; $i++) {

        $interestThisMonth = round($openingBalance * $monthlyRate, 2);

        $principalThisMonth = $principalPerMonth;

        if ($i === $termMonths) {
            $principalThisMonth = round($openingBalance, 2);
        }

        $closingBalance = $openingBalance - $principalThisMonth;

        $cumulInterest += $interestThisMonth;
        $cumulPrincipal += $principalThisMonth;

        $schedule[] = [
            'payment_number'       => $i,
            'payment_date'         => $currentDate->format('Y-m-d'),

            'opening_balance'      => round($openingBalance, 2),
            'principal_amount'     => $principalThisMonth,
            'interest_amount'      => $interestThisMonth,

            'payment_amount'       => $actualInstallment,

            'm_interest'           => $mInterest,
            'cumulative_interest'  => round($cumulInterest, 2),
            'cumulative_principal' => round($cumulPrincipal, 2),

            'closing_balance'      => round(max(0, $closingBalance), 2),
        ];

        // fix rounding difference on last row
        if ($i === $termMonths) {
            $difference = round($principal - $cumulPrincipal, 2);
            $schedule[$i - 1]['principal_amount'] += $difference;
            $schedule[$i - 1]['closing_balance'] = 0;
        }

        $openingBalance = $closingBalance;
        $currentDate->addMonth();
    }

    return $schedule;
}

public function isFullyGuaranteed()
{
    return $this->guarantors()->where('status', '!=', 'approved')->count() === 0;
}

public function guarantorRequestPage(Loan $loan)
{
    $user = auth()->user();

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

    // If all guarantors approved
    $pending = LoanGuarantor::where('loan_id', $loan->id)
        ->where('status', 'pending')
        ->count();

    if ($pending == 0) {
        $loan->update([
            'status' => 'pending'
        ]);
    }

    return redirect()->back()->with('success', 'Loan guarantee approved successfully.');
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

    // Optional: reject whole loan immediately
    $loan->update([
        'status' => 'guarantor_rejected'
    ]);

    return redirect()->back()->with('error', 'You rejected this guarantor request.');
}
}