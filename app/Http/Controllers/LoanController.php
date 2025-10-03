<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\LoanGuarantor;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\PaymentVoucher;
use App\Models\Account;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
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
            'filters' => $request->only(['status', 'search', 'member_id', 'loan_product_id', 'date_from', 'date_to'])
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
                'user' => auth()->user()->load('member') 
            ]
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
            'guarantors' => 'sometimes|array',
            'guarantors.*.member_id' => 'required_with:guarantors|exists:members,id',
            'guarantors.*.guaranteed_amount' => 'required_with:guarantors|numeric|min:0',
            'documents' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $loanProduct = LoanProduct::findOrFail($request->loan_product_id);
            $member = Member::findOrFail($request->member_id);

            // Validate loan amount against product limits
            if (
                $request->applied_amount < $loanProduct->min_amount ||
                $request->applied_amount > $loanProduct->max_amount
            ) {
                return response()->json([
                    'success' => false,
                    'message' => "Loan amount must be between {$loanProduct->min_amount->toFixed(2)} and {$loanProduct->max_amount->toFixed(2)}"
                ], 422);
            }

            // Validate term against product limits
            if (
                $request->term_months < $loanProduct->min_term_months ||
                $request->term_months > $loanProduct->max_term_months
            ) {
                return response()->json([
                    'success' => false,
                    'message' => "Loan term must be between {$loanProduct->min_term_months} and {$loanProduct->max_term_months} months"
                ], 422);
            }

            // Check if member has existing active loans
            $existingLoans = Loan::where('member_id', $request->member_id)
                ->whereIn('status', ['pending', 'approved', 'disbursed'])
                ->count();

            if ($existingLoans > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member has existing active loan applications'
                ], 422);
            }

            // Calculate loan details
            $processingFee = ($request->applied_amount * $loanProduct->processing_fee_rate) / 100;
            $insuranceFee = ($request->applied_amount * $loanProduct->insurance_rate) / 100;
            $monthlyInterestRate = $loanProduct->interest_rate / 100 / 12;
            $monthlyRepayment = $this->calculateMonthlyRepayment(
                $request->applied_amount,
                $monthlyInterestRate,
                $request->term_months
            );
            $totalRepayable = $monthlyRepayment * $request->term_months;
            

            // Create loan
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
                'status' => 'pending',
                'application_date' => now(),
                'documents' => $request->documents ?? [],
                'outstanding_balance' => $request->applied_amount,
                'principal_balance' => $request->applied_amount,
                'interest_balance' => $totalRepayable - $request->applied_amount,
                'penalty_balance' => 0,
                'days_in_arrears' => 0
            ]);

            // Add guarantors if provided
            if ($request->has('guarantors') && is_array($request->guarantors)) {
                foreach ($request->guarantors as $guarantorData) {
                    LoanGuarantor::create([
                        'loan_id' => $loan->id,
                        'guarantor_member_id' => $guarantorData['member_id'],
                        'guaranteed_amount' => $guarantorData['guaranteed_amount'],
                        'status' => 'pending'
                    ]);
                }
            }

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'loan_application_created',
                'model_type' => 'App\Models\Loan',
                'model_id' => $loan->id,
                'new_values' => $loan->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan application submitted successfully',
                'data' => $loan->load(['member', 'loanProduct', 'guarantors.guarantorMember'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating loan application: ' . $e->getMessage()
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
            'disbursedBy'
        ])->findOrFail($id);

        return Inertia::render('Shared/Loans/Show', [
            'loan' => $loan
        ]);
        
    }


    /**
     * Show loan repayments
     */
    public function repayments($id)
    {
        $loan = Loan::with(['member.user', 'loanProduct'])->findOrFail($id);
        $repayments = LoanRepayment::where('loan_id', $id)
            ->orderBy('due_date')
            ->get();

        return Inertia::render('Shared/Loans/Repayment', [
            'loan' => $loan,
            'repayments' => $repayments
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
            'members' => $members
        ]);
    }

    /**
     * Update the specified loan
     */
    public function update(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);

        // Only allow updates for pending loans
        if ($loan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending loans can be updated'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'applied_amount' => 'sometimes|numeric|min:1',
            'term_months' => 'sometimes|integer|min:1',
            'purpose' => 'sometimes|string|max:500',
            'documents' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldValues = $loan->toArray();
            $loan->update($request->only([
                'applied_amount',
                'term_months',
                'purpose',
                'documents'
            ]));

            // Recalculate loan details if amount or term changed
            if ($request->has('applied_amount') || $request->has('term_months')) {
                $loanProduct = $loan->loanProduct;
                $processingFee = ($loan->applied_amount * $loanProduct->processing_fee_rate) / 100;
                $insuranceFee = ($loan->applied_amount * $loanProduct->insurance_rate) / 100;
                $monthlyInterestRate = $loanProduct->interest_rate / 100 / 12;
                $monthlyRepayment = $this->calculateMonthlyRepayment(
                    $loan->applied_amount,
                    $monthlyInterestRate,
                    $loan->term_months
                );
                $totalRepayable = $monthlyRepayment * $loan->term_months;

                $loan->update([
                    'monthly_repayment' => $monthlyRepayment,
                    'total_repayable' => $totalRepayable,
                    'processing_fee' => $processingFee,
                    'insurance_fee' => $insuranceFee,
                    'outstanding_balance' => $loan->applied_amount,
                    'principal_balance' => $loan->applied_amount,
                    'interest_balance' => $totalRepayable - $loan->applied_amount
                ]);
            }

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'loan_updated',
                'model_type' => 'App\Models\Loan',
                'model_id' => $loan->id,
                'old_values' => $oldValues,
                'new_values' => $loan->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan updated successfully',
                'data' => $loan->load(['member', 'loanProduct'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating loan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a loan application
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'approved_amount' => 'required|numeric|min:1',
            'approval_notes' => 'sometimes|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending loans can be approved'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldValues = $loan->toArray();

            // Update loan with approval details
            $loan->update([
                'approved_amount' => $request->approved_amount,
                'status' => 'approved',
                'approval_date' => now(),
                'approved_by' => Auth::id(),
                'approval_notes' => $request->approval_notes
            ]);

            // Recalculate loan details based on approved amount
            $loanProduct = $loan->loanProduct;
            $processingFee = ($request->approved_amount * $loanProduct->processing_fee_rate) / 100;
            $insuranceFee = ($request->approved_amount * $loanProduct->insurance_rate) / 100;
            $monthlyInterestRate = $loanProduct->interest_rate / 100 / 12;
            $monthlyRepayment = $this->calculateMonthlyRepayment(
                $request->approved_amount,
                $monthlyInterestRate,
                $loan->term_months
            );
            $totalRepayable = $monthlyRepayment * $loan->term_months;

            $loan->update([
                'monthly_repayment' => $monthlyRepayment,
                'total_repayable' => $totalRepayable,
                'processing_fee' => $processingFee,
                'insurance_fee' => $insuranceFee,
                'outstanding_balance' => $request->approved_amount,
                'principal_balance' => $request->approved_amount,
                'interest_balance' => $totalRepayable - $request->approved_amount
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'loan_approved',
                'model_type' => 'App\Models\Loan',
                'model_id' => $loan->id,
                'old_values' => $oldValues,
                'new_values' => $loan->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan approved successfully',
                'data' => $loan->load(['member', 'loanProduct'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error approving loan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a loan application
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $loan = Loan::findOrFail($id);

        if (!in_array($loan->status, ['pending', 'approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or approved loans can be rejected'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldValues = $loan->toArray();

            $loan->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'approved_by' => Auth::id(),
                'approval_date' => now()
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
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan rejected successfully',
                'data' => $loan->load(['member', 'loanProduct'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting loan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disburse an approved loan
     */
    public function disburse(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'disbursed_amount' => 'required|numeric|min:1',
            'disbursement_method' => 'required|in:cash,mobile_money,bank_transfer',
            'disbursement_reference' => 'sometimes|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved loans can be disbursed'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldValues = $loan->toArray();
            $member = $loan->member;

            // Get or create member's savings account
            $savingsAccount = Account::firstOrCreate([
                'member_id' => $member->id,
                'account_type' => 'savings'
            ], [
                'account_number' => $this->generateAccountNumber(),
                'balance' => 0,
                'available_balance' => 0,
                'is_active' => true
            ]);

            // Calculate net disbursement (after fees)
            $netDisbursement = $request->disbursed_amount - $loan->processing_fee - $loan->insurance_fee;

            // Create disbursement transaction
            $transaction = Transaction::create([
                'transaction_id' => $this->generateTransactionId(),
                'account_id' => $savingsAccount->id,
                'member_id' => $member->id,
                'transaction_type' => 'loan_disbursement',
                'amount' => $netDisbursement,
                'balance_before' => $savingsAccount->balance,
                'balance_after' => $savingsAccount->balance + $netDisbursement,
                'description' => "Loan disbursement for loan {$loan->loan_number}",
                'reference_number' => $request->disbursement_reference,
                'payment_method' => $request->disbursement_method,
                'payment_reference' => $request->disbursement_reference,
                'status' => 'completed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'metadata' => [
                    'loan_id' => $loan->id,
                    'loan_number' => $loan->loan_number,
                    'gross_amount' => $request->disbursed_amount,
                    'processing_fee' => $loan->processing_fee,
                    'insurance_fee' => $loan->insurance_fee,
                    'net_amount' => $netDisbursement
                ]
            ]);

            // Update account balance
            $savingsAccount->update([
                'balance' => $savingsAccount->balance + $netDisbursement,
                'available_balance' => $savingsAccount->available_balance + $netDisbursement,
                'last_transaction_at' => now()
            ]);

            // Update loan status
            $loan->update([
                'disbursed_amount' => $request->disbursed_amount,
                'status' => 'disbursed',
                'disbursement_date' => now(),
                'disbursed_by' => Auth::id(),
                'first_repayment_date' => now()->addMonth(),
                'maturity_date' => now()->addMonths($loan->term_months)
            ]);

            // Generate repayment schedule
            $this->generateRepaymentSchedule($loan);

            // Create payment voucher for record keeping
            PaymentVoucher::create([
                'voucher_number' => $this->generateVoucherNumber(),
                'voucher_type' => 'loan_disbursement',
                'payee_name' => $member->first_name . ' ' . $member->last_name,
                'payee_phone' => $member->user->phone,
                'amount' => $request->disbursed_amount,
                'purpose' => 'Loan disbursement',
                'description' => "Disbursement for loan {$loan->loan_number}",
                'loan_id' => $loan->id,
                'status' => 'paid',
                'created_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'paid_by' => Auth::id(),
                'approval_date' => now(),
                'payment_date' => now()
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'loan_disbursed',
                'model_type' => 'App\Models\Loan',
                'model_id' => $loan->id,
                'old_values' => $oldValues,
                'new_values' => $loan->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan disbursed successfully',
                'data' => [
                    'loan' => $loan->load(['member', 'loanProduct']),
                    'transaction' => $transaction,
                    'net_disbursement' => $netDisbursement
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error disbursing loan: ' . $e->getMessage()
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
            'this_month_disbursements' => Loan::whereMonth('disbursement_date', now()->month)->count()
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
     * Generate repayment schedule for a loan
     */
    private function generateRepaymentSchedule(Loan $loan)
    {
        $startDate = Carbon::parse($loan->first_repayment_date);
        $monthlyRepayment = $loan->monthly_repayment;
        $remainingBalance = $loan->disbursed_amount;
        $monthlyInterestRate = $loan->interest_rate / 100 / 12;

        for ($i = 1; $i <= $loan->term_months; $i++) {
            $dueDate = $startDate->copy()->addMonths($i - 1);
            $interestAmount = $remainingBalance * $monthlyInterestRate;
            $principalAmount = $monthlyRepayment - $interestAmount;

            // Adjust last payment to clear any remaining balance
            if ($i == $loan->term_months) {
                $principalAmount = $remainingBalance;
                $monthlyRepayment = $principalAmount + $interestAmount;
            }

            LoanRepayment::create([
                'loan_id' => $loan->id,
                'due_date' => $dueDate,
                'expected_amount' => $monthlyRepayment,
                'principal_amount' => $principalAmount,
                'interest_amount' => $interestAmount,
                'penalty_amount' => 0,
                'paid_amount' => 0,
                'outstanding_amount' => $monthlyRepayment,
                'status' => 'pending',
                'days_late' => 0
            ]);

            $remainingBalance -= $principalAmount;
        }
    }
}