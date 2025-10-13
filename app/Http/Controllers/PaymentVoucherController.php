<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Models\BudgetItem;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Carbon\Carbon;

class PaymentVoucherController extends Controller
{
    /**
     * Display a listing of payment vouchers.
     */
    public function index(Request $request)
    {
        $query = PaymentVoucher::with(['creator', 'approver', 'payer', 'budgetItem', 'loan.member'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('voucher_type')) {
            $query->where('voucher_type', $request->voucher_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_number', 'like', "%{$search}%")
                  ->orWhere('payee_name', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        $vouchers = $query->paginate(20);

        // Get summary statistics
        $stats = [
            'total_vouchers' => PaymentVoucher::count(),
            'pending_vouchers' => PaymentVoucher::where('status', 'pending')->count(),
            'approved_vouchers' => PaymentVoucher::where('status', 'approved')->count(),
            'paid_vouchers' => PaymentVoucher::where('status', 'paid')->count(),
            'total_pending_amount' => PaymentVoucher::where('status', 'pending')->sum('amount'),
            'total_approved_amount' => PaymentVoucher::where('status', 'approved')->sum('amount'),
            'total_paid_amount' => PaymentVoucher::where('status', 'paid')->sum('amount'),
        ];

        return Inertia::render('Shared/PaymentVouchers/Index', [
            'vouchers' => $vouchers,
            'stats' => $stats,
            'filters' => $request->only(['status', 'voucher_type', 'date_from', 'date_to', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new payment voucher.
     */
    public function create()
    {
        $budgetItems = BudgetItem::with('budget')
            ->whereHas('budget', function ($query) {
                $query->where('status', 'active');
            })
            ->where('remaining_amount', '>', 0)
            ->get();

        $pendingLoans = Loan::with('member')
            ->where('status', 'approved')
            ->get();

        return Inertia::render('Shared/PaymentVouchers/Create', [
            'budgetItems' => $budgetItems,
            'pendingLoans' => $pendingLoans,
            'voucherTypes' => $this->getVoucherTypes(),
        ]);
    }

    /**
     * Store a newly created payment voucher.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'voucher_type' => 'required|in:loan_disbursement,operational_expense,dividend_payment,refund,other',
            'payee_name' => 'required|string|max:255',
            'payee_phone' => 'nullable|string|max:20',
            'payee_account' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'purpose' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget_item_id' => 'nullable|exists:budget_items,id',
            'loan_id' => 'nullable|exists:loans,id',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();

        try {
            // Generate voucher number
            $voucherNumber = $this->generateVoucherNumber();

            // Handle document uploads
            $documents = [];
            if ($request->hasFile('supporting_documents')) {
                foreach ($request->file('supporting_documents') as $file) {
                    $path = $file->store('vouchers/documents', 'public');
                    $documents[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType(),
                        'uploaded_at' => now(),
                    ];
                }
            }

            // Validate budget item availability
            if ($request->budget_item_id) {
                $budgetItem = BudgetItem::find($request->budget_item_id);
                if ($budgetItem && $budgetItem->remaining_amount < $request->amount) {
                    throw new \Exception('Insufficient budget allocation. Available: ' . number_format($budgetItem->remaining_amount, 2));
                }
            }

            // Create voucher
            $voucher = PaymentVoucher::create([
                'voucher_number' => $voucherNumber,
                'voucher_type' => $request->voucher_type,
                'payee_name' => $request->payee_name,
                'payee_phone' => $request->payee_phone,
                'payee_account' => $request->payee_account,
                'amount' => $request->amount,
                'purpose' => $request->purpose,
                'description' => $request->description,
                'budget_item_id' => $request->budget_item_id,
                'loan_id' => $request->loan_id,
                'status' => 'pending',
                'created_by' => Auth::id(),
                'supporting_documents' => $documents,
            ]);

            // Auto-approve for certain user roles and amounts
            if ($this->shouldAutoApprove($request->amount, Auth::user()->role)) {
                $voucher->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approval_date' => now(),
                    'approval_notes' => 'Auto-approved based on user role and amount threshold',
                ]);
            }

            DB::commit();

            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Payment voucher created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded files on error
            if (!empty($documents)) {
                foreach ($documents as $doc) {
                    Storage::disk('public')->delete($doc['path']);
                }
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified payment voucher.
     */
    public function show(PaymentVoucher $voucher)
    {
        $voucher->load(['creator', 'approver', 'payer', 'budgetItem.budget', 'loan.member']);

        return Inertia::render('Shared/PaymentVouchers/Show', [
            'voucher' => $voucher,
            'canApprove' => $this->canApprove($voucher),
            'canPay' => $this->canPay($voucher),
            'canEdit' => $this->canEdit($voucher),
            'canDelete' => $this->canDelete($voucher),
        ]);
    }

    /**
     * Show the form for editing the specified payment voucher.
     */
    public function edit(PaymentVoucher $voucher)
    {
        if (!$this->canEdit($voucher)) {
            return redirect()->back()->withErrors(['error' => 'You cannot edit this voucher.']);
        }

        $budgetItems = BudgetItem::with('budget')
            ->whereHas('budget', function ($query) {
                $query->where('status', 'active');
            })
            ->where('remaining_amount', '>', 0)
            ->get();

        $pendingLoans = Loan::with('member')
            ->where('status', 'approved')
            ->get();

        return Inertia::render('Shared/PaymentVouchers/Edit', [
            'voucher' => $voucher,
            'budgetItems' => $budgetItems,
            'pendingLoans' => $pendingLoans,
            'voucherTypes' => $this->getVoucherTypes(),
        ]);
    }

    /**
     * Update the specified payment voucher.
     */
    public function update(Request $request, PaymentVoucher $voucher)
    {
        if (!$this->canEdit($voucher)) {
            return redirect()->back()->withErrors(['error' => 'You cannot edit this voucher.']);
        }

        $validator = Validator::make($request->all(), [
            'voucher_type' => 'required|in:loan_disbursement,operational_expense,dividend_payment,refund,other',
            'payee_name' => 'required|string|max:255',
            'payee_phone' => 'nullable|string|max:20',
            'payee_account' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'purpose' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget_item_id' => 'nullable|exists:budget_items,id',
            'loan_id' => 'nullable|exists:loans,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();

        try {
            // Validate budget item availability
            if ($request->budget_item_id && $request->budget_item_id != $voucher->budget_item_id) {
                $budgetItem = BudgetItem::find($request->budget_item_id);
                if ($budgetItem && $budgetItem->remaining_amount < $request->amount) {
                    throw new \Exception('Insufficient budget allocation. Available: ' . number_format($budgetItem->remaining_amount, 2));
                }
            }

            $voucher->update([
                'voucher_type' => $request->voucher_type,
                'payee_name' => $request->payee_name,
                'payee_phone' => $request->payee_phone,
                'payee_account' => $request->payee_account,
                'amount' => $request->amount,
                'purpose' => $request->purpose,
                'description' => $request->description,
                'budget_item_id' => $request->budget_item_id,
                'loan_id' => $request->loan_id,
            ]);

            DB::commit();

            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Payment voucher updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified payment voucher.
     */
    public function destroy(PaymentVoucher $voucher)
    {
        if (!$this->canDelete($voucher)) {
            return redirect()->back()->withErrors(['error' => 'You cannot delete this voucher.']);
        }

        DB::beginTransaction();

        try {
            // Delete associated documents
            if ($voucher->supporting_documents) {
                foreach ($voucher->supporting_documents as $doc) {
                    Storage::disk('public')->delete($doc['path']);
                }
            }

            $voucher->delete();

            DB::commit();

            return redirect()->route('vouchers.index')
                ->with('success', 'Payment voucher deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Submit voucher for approval.
     */
    public function submit(PaymentVoucher $voucher)
    {
        if ($voucher->status !== 'pending') {
            return redirect()->back()->withErrors(['error' => 'Only pending vouchers can be submitted.']);
        }

        // Additional validation can be added here
        $voucher->update([
            'status' => 'pending',
            'updated_at' => now(),
        ]);

        // Send notification to approvers
        $this->notifyApprovers($voucher);

        return redirect()->route('vouchers.show', $voucher)
            ->with('success', 'Voucher submitted for approval.');
    }

    /**
     * Approve the payment voucher.
     */
    public function approve(Request $request, PaymentVoucher $voucher)
    {
        if (!$this->canApprove($voucher)) {
            return redirect()->back()->withErrors(['error' => 'You cannot approve this voucher.']);
        }

        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();

        try {
            $voucher->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approval_date' => now(),
                'approval_notes' => $request->approval_notes,
            ]);

            DB::commit();

            // Send notification to creator
            $this->notifyVoucherStatusChange($voucher, 'approved');

            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Payment voucher approved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject the payment voucher.
     */
    public function reject(Request $request, PaymentVoucher $voucher)
    {
        if (!$this->canApprove($voucher)) {
            return redirect()->back()->withErrors(['error' => 'You cannot reject this voucher.']);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();

        try {
            $voucher->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approval_date' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            DB::commit();

            // Send notification to creator
            $this->notifyVoucherStatusChange($voucher, 'rejected');

            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Payment voucher rejected.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Process payment for the voucher.
     */
    public function pay(Request $request, PaymentVoucher $voucher)
    {
        if (!$this->canPay($voucher)) {
            return redirect()->back()->withErrors(['error' => 'You cannot process payment for this voucher.']);
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,mobile_money,bank_transfer,cheque',
            'payment_reference' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();

        try {
            // Update voucher status
            $voucher->update([
                'status' => 'paid',
                'paid_by' => Auth::id(),
                'payment_date' => now(),
            ]);

            // Create transaction record
            $transaction = $this->createPaymentTransaction($voucher, $request);

            // Update budget item spent amount
            if ($voucher->budget_item_id) {
                $budgetItem = BudgetItem::find($voucher->budget_item_id);
                $budgetItem->increment('spent_amount', $voucher->amount);
                $budgetItem->decrement('remaining_amount', $voucher->amount);
            }

            // Handle specific voucher types
            $this->handleVoucherTypePayment($voucher, $transaction);

            DB::commit();

            // Send notification to creator
            $this->notifyVoucherStatusChange($voucher, 'paid');

            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Payment processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel the payment voucher.
     */
    public function cancel(Request $request, PaymentVoucher $voucher)
    {
        if (!in_array($voucher->status, ['pending', 'approved'])) {
            return redirect()->back()->withErrors(['error' => 'Only pending or approved vouchers can be cancelled.']);
        }

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();

        try {
            $voucher->update([
                'status' => 'cancelled',
                'rejection_reason' => $request->cancellation_reason,
                'updated_at' => now(),
            ]);

            DB::commit();

            // Send notification to relevant parties
            $this->notifyVoucherStatusChange($voucher, 'cancelled');

            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Payment voucher cancelled.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Upload supporting documents.
     */
    public function uploadDocuments(Request $request, PaymentVoucher $voucher)
    {
        $validator = Validator::make($request->all(), [
            'documents' => 'required|array',
            'documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();

        try {
            $existingDocuments = $voucher->supporting_documents ?? [];
            $newDocuments = [];

            foreach ($request->file('documents') as $file) {
                $path = $file->store('vouchers/documents', 'public');
                $newDocuments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                    'uploaded_at' => now(),
                ];
            }

            $voucher->update([
                'supporting_documents' => array_merge($existingDocuments, $newDocuments),
            ]);

            DB::commit();

            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Documents uploaded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a supporting document.
     */
    public function deleteDocument(PaymentVoucher $voucher, $document)
    {
        $documents = $voucher->supporting_documents ?? [];
        $documentIndex = null;

        foreach ($documents as $index => $doc) {
            if ($doc['path'] === $document) {
                $documentIndex = $index;
                break;
            }
        }

        if ($documentIndex === null) {
            return redirect()->back()->withErrors(['error' => 'Document not found.']);
        }

        DB::beginTransaction();

        try {
            // Delete file from storage
            Storage::disk('public')->delete($documents[$documentIndex]['path']);

            // Remove from array
            unset($documents[$documentIndex]);
            $documents = array_values($documents);

            $voucher->update([
                'supporting_documents' => $documents,
            ]);

            DB::commit();

            return redirect()->route('vouchers.show', $voucher)
                ->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate pending vouchers report.
     */
    public function pendingReport(Request $request)
    {
        $query = PaymentVoucher::with(['creator', 'budgetItem', 'loan.member'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $vouchers = $query->get();
        $totalAmount = $vouchers->sum('amount');

        return Inertia::render('Vouchers/Reports/Pending', [
            'vouchers' => $vouchers,
            'totalAmount' => $totalAmount,
            'filters' => $request->only(['date_from', 'date_to']),
        ]);
    }

    /**
     * Generate approved vouchers report.
     */
    public function approvedReport(Request $request)
    {
        $query = PaymentVoucher::with(['creator', 'approver', 'budgetItem', 'loan.member'])
            ->where('status', 'approved')
            ->orderBy('approval_date', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('approval_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('approval_date', '<=', $request->date_to);
        }

        $vouchers = $query->get();
        $totalAmount = $vouchers->sum('amount');

        return Inertia::render('Vouchers/Reports/Approved', [
            'vouchers' => $vouchers,
            'totalAmount' => $totalAmount,
            'filters' => $request->only(['date_from', 'date_to']),
        ]);
    }

    /**
     * Generate paid vouchers report.
     */
    public function paidReport(Request $request)
    {
        $query = PaymentVoucher::with(['creator', 'approver', 'payer', 'budgetItem', 'loan.member'])
            ->where('status', 'paid')
            ->orderBy('payment_date', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $vouchers = $query->get();
        $totalAmount = $vouchers->sum('amount');

        return Inertia::render('Vouchers/Reports/Paid', [
            'vouchers' => $vouchers,
            'totalAmount' => $totalAmount,
            'filters' => $request->only(['date_from', 'date_to']),
        ]);
    }

    /**
     * Helper methods
     */

    private function generateVoucherNumber()
    {
        $year = date('Y');
        $month = date('m');
        $lastVoucher = PaymentVoucher::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastVoucher ? (int)substr($lastVoucher->voucher_number, -4) + 1 : 1;
        
        return sprintf('PV%s%s%04d', $year, $month, $sequence);
    }

    private function getVoucherTypes()
    {
        return [
            'loan_disbursement' => 'Loan Disbursement',
            'operational_expense' => 'Operational Expense',
            'dividend_payment' => 'Dividend Payment',
            'refund' => 'Refund',
            'other' => 'Other',
        ];
    }

    private function shouldAutoApprove($amount, $userRole)
    {
        // Auto-approve logic based on amount and user role
        $thresholds = [
            'admin' => 1000000,
            'management' => 500000,
            'accountant' => 100000,
            'loan_officer' => 50000,
        ];

        return isset($thresholds[$userRole]) && $amount <= $thresholds[$userRole];
    }

    private function canApprove(PaymentVoucher $voucher)
    {
        $user = Auth::user();
        
        // Cannot approve own vouchers
        if ($voucher->created_by === $user->id) {
            return false;
        }

        // Only certain roles can approve
        if (!in_array($user->role, ['admin', 'management', 'accountant'])) {
            return false;
        }

        // Only pending vouchers can be approved
        return $voucher->status === 'pending';
    }

    private function canPay(PaymentVoucher $voucher)
    {
        $user = Auth::user();
        
        // Only certain roles can process payments
        if (!in_array($user->role, ['admin', 'management', 'accountant'])) {
            return false;
        }

        // Only approved vouchers can be paid
        return $voucher->status === 'approved';
    }

    private function canEdit(PaymentVoucher $voucher)
    {
        $user = Auth::user();
        
        // Only creator can edit, or admin/management
        if ($voucher->created_by !== $user->id && !in_array($user->role, ['admin', 'management'])) {
            return false;
        }

        // Only pending vouchers can be edited
        return $voucher->status === 'pending';
    }

    private function canDelete(PaymentVoucher $voucher)
    {
        $user = Auth::user();
        
        // Only creator can delete, or admin
        if ($voucher->created_by !== $user->id && $user->role !== 'admin') {
            return false;
        }

        // Only pending vouchers can be deleted
        return $voucher->status === 'pending';
    }

    private function createPaymentTransaction(PaymentVoucher $voucher, Request $request)
    {
        // This would create a transaction record
        // Implementation depends on your specific transaction structure
        return Transaction::create([
            'transaction_id' => 'TXN' . time(),
            'transaction_type' => 'voucher_payment',
            'amount' => $voucher->amount,
            'description' => 'Payment for voucher: ' . $voucher->voucher_number,
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'status' => 'completed',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'metadata' => [
                'voucher_id' => $voucher->id,
                'voucher_number' => $voucher->voucher_number,
                'payment_notes' => $request->payment_notes,
            ],
        ]);
    }

    private function handleVoucherTypePayment(PaymentVoucher $voucher, Transaction $transaction)
    {
        switch ($voucher->voucher_type) {
            case 'loan_disbursement':
                if ($voucher->loan_id) {
                    $loan = Loan::find($voucher->loan_id);
                    $loan->update([
                        'status' => 'disbursed',
                        'disbursed_amount' => $voucher->amount,
                        'disbursement_date' => now(),
                        'disbursed_by' => Auth::id(),
                    ]);
                }
                break;
            case 'dividend_payment':
                // Handle dividend payment logic
                break;
            // Add other voucher type handlers as needed
        }
    }

    private function notifyApprovers(PaymentVoucher $voucher)
    {
        // Send notifications to users who can approve
        $approvers = User::whereIn('role', ['admin', 'management', 'accountant'])
            ->where('id', '!=', $voucher->created_by)
            ->where('is_active', true)
            ->get();

        foreach ($approvers as $approver) {
            // Create notification
            $approver->notifications()->create([
                'title' => 'Payment Voucher Pending Approval',
                'message' => "Voucher {$voucher->voucher_number} for {$voucher->payee_name} requires approval.",
                'type' => 'voucher',
                'metadata' => [
                    'voucher_id' => $voucher->id,
                    'amount' => $voucher->amount,
                    'type' => $voucher->voucher_type,
                ],
            ]);
        }
    }

 private function notifyVoucherStatusChange(PaymentVoucher $voucher, $status)
    {
        // Notify creator of status change
        $creator = User::find($voucher->created_by);
        if ($creator) {
            $creator->notifications()->create([
                'title' => 'Payment Voucher ' . ucfirst($status),
                'message' => "Your voucher {$voucher->voucher_number} has been {$status}.",
                'type' => 'voucher',
                'metadata' => [
                    'voucher_id' => $voucher->id,
                    'amount' => $voucher->amount,
                    'status' => $status,
                    'voucher_type' => $voucher->voucher_type,
                ],
            ]);
        }

        // Also notify relevant parties based on voucher type
        if ($voucher->voucher_type === 'loan_disbursement' && $voucher->loan_id) {
            $loan = Loan::with('member.user')->find($voucher->loan_id);
            if ($loan && $loan->member && $loan->member->user) {
                $loan->member->user->notifications()->create([
                    'title' => 'Loan Disbursement ' . ucfirst($status),
                    'message' => "Disbursement voucher for your loan has been {$status}.",
                    'type' => 'loan',
                    'metadata' => [
                        'loan_id' => $loan->id,
                        'voucher_id' => $voucher->id,
                        'amount' => $voucher->amount,
                        'status' => $status,
                    ],
                ]);
            }
        }
    }

    /**
     * Get voucher statistics for dashboard
     */
    public function getStats()
    {
        $stats = [
            'total_vouchers' => PaymentVoucher::count(),
            'pending_count' => PaymentVoucher::where('status', 'pending')->count(),
            'approved_count' => PaymentVoucher::where('status', 'approved')->count(),
            'paid_count' => PaymentVoucher::where('status', 'paid')->count(),
            'rejected_count' => PaymentVoucher::where('status', 'rejected')->count(),
            'cancelled_count' => PaymentVoucher::where('status', 'cancelled')->count(),
            'pending_amount' => PaymentVoucher::where('status', 'pending')->sum('amount'),
            'approved_amount' => PaymentVoucher::where('status', 'approved')->sum('amount'),
            'paid_amount' => PaymentVoucher::where('status', 'paid')->sum('amount'),
            'this_month_count' => PaymentVoucher::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'this_month_amount' => PaymentVoucher::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Download voucher as PDF
     */
    public function downloadPdf(PaymentVoucher $voucher)
    {
        $voucher->load(['creator', 'approver', 'payer', 'budgetItem.budget', 'loan.member']);

        // This would generate a PDF of the voucher
        // You would need to implement PDF generation using a library like DOMPDF or similar
        // For now, returning a view that can be printed
        return view('vouchers.pdf', compact('voucher'));
    }

    /**
     * Export vouchers to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = PaymentVoucher::with(['creator', 'approver', 'payer', 'budgetItem', 'loan.member']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('voucher_type')) {
            $query->where('voucher_type', $request->voucher_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $vouchers = $query->orderBy('created_at', 'desc')->get();

        // This would export to Excel using Laravel Excel or similar
        // For now, returning CSV format
        $filename = 'payment_vouchers_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($vouchers) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Voucher Number',
                'Type',
                'Payee Name',
                'Amount',
                'Purpose',
                'Status',
                'Created By',
                'Created At',
                'Approved By',
                'Approval Date',
                'Payment Date',
                'Budget Item',
            ]);

            // CSV data
            foreach ($vouchers as $voucher) {
                fputcsv($file, [
                    $voucher->voucher_number,
                    $voucher->voucher_type,
                    $voucher->payee_name,
                    $voucher->amount,
                    $voucher->purpose,
                    $voucher->status,
                    $voucher->creator->name ?? '',
                    $voucher->created_at->format('Y-m-d H:i:s'),
                    $voucher->approver->name ?? '',
                    $voucher->approval_date ? $voucher->approval_date->format('Y-m-d H:i:s') : '',
                    $voucher->payment_date ? $voucher->payment_date->format('Y-m-d H:i:s') : '',
                    $voucher->budgetItem->name ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk approve vouchers
     */
    public function bulkApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'voucher_ids' => 'required|array',
            'voucher_ids.*' => 'exists:payment_vouchers,id',
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();

        try {
            $vouchers = PaymentVoucher::whereIn('id', $request->voucher_ids)
                ->where('status', 'pending')
                ->get();

            $approvedCount = 0;
            $errors = [];

            foreach ($vouchers as $voucher) {
                if ($this->canApprove($voucher)) {
                    $voucher->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approval_date' => now(),
                        'approval_notes' => $request->approval_notes,
                    ]);

                    $this->notifyVoucherStatusChange($voucher, 'approved');
                    $approvedCount++;
                } else {
                    $errors[] = "Cannot approve voucher {$voucher->voucher_number}";
                }
            }

            DB::commit();

            $message = "{$approvedCount} vouchers approved successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Bulk reject vouchers
     */
    public function bulkReject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'voucher_ids' => 'required|array',
            'voucher_ids.*' => 'exists:payment_vouchers,id',
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();

        try {
            $vouchers = PaymentVoucher::whereIn('id', $request->voucher_ids)
                ->where('status', 'pending')
                ->get();

            $rejectedCount = 0;
            $errors = [];

            foreach ($vouchers as $voucher) {
                if ($this->canApprove($voucher)) {
                    $voucher->update([
                        'status' => 'rejected',
                        'approved_by' => Auth::id(),
                        'approval_date' => now(),
                        'rejection_reason' => $request->rejection_reason,
                    ]);

                    $this->notifyVoucherStatusChange($voucher, 'rejected');
                    $rejectedCount++;
                } else {
                    $errors[] = "Cannot reject voucher {$voucher->voucher_number}";
                }
            }

            DB::commit();

            $message = "{$rejectedCount} vouchers rejected successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get voucher summary by type
     */
    public function getSummaryByType(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth();
        $dateTo = $request->date_to ?? now()->endOfMonth();

        $summary = PaymentVoucher::selectRaw('
            voucher_type,
            COUNT(*) as count,
            SUM(amount) as total_amount,
            SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount,
            SUM(CASE WHEN status = "approved" THEN amount ELSE 0 END) as approved_amount,
            SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid_amount
        ')
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->groupBy('voucher_type')
        ->get();

        return response()->json($summary);
    }

    /**
     * Get voucher approval workflow
     */
    public function getApprovalWorkflow(PaymentVoucher $voucher)
    {
        $workflow = [
            [
                'step' => 'Created',
                'status' => 'completed',
                'user' => $voucher->creator->name ?? 'Unknown',
                'date' => $voucher->created_at,
                'notes' => 'Voucher created',
            ]
        ];

        if ($voucher->status === 'approved' || $voucher->status === 'rejected' || $voucher->status === 'paid') {
            $workflow[] = [
                'step' => $voucher->status === 'rejected' ? 'Rejected' : 'Approved',
                'status' => 'completed',
                'user' => $voucher->approver->name ?? 'Unknown',
                'date' => $voucher->approval_date,
                'notes' => $voucher->status === 'rejected' ? $voucher->rejection_reason : $voucher->approval_notes,
            ];
        }

        if ($voucher->status === 'paid') {
            $workflow[] = [
                'step' => 'Paid',
                'status' => 'completed',
                'user' => $voucher->payer->name ?? 'Unknown',
                'date' => $voucher->payment_date,
                'notes' => 'Payment processed',
            ];
        }

        return response()->json($workflow);
    }

    /**
     * Duplicate voucher
     */
    public function duplicate(PaymentVoucher $voucher)
    {
        $newVoucher = $voucher->replicate();
        $newVoucher->voucher_number = $this->generateVoucherNumber();
        $newVoucher->status = 'pending';
        $newVoucher->created_by = Auth::id();
        $newVoucher->approved_by = null;
        $newVoucher->paid_by = null;
        $newVoucher->approval_date = null;
        $newVoucher->payment_date = null;
        $newVoucher->approval_notes = null;
        $newVoucher->rejection_reason = null;
        $newVoucher->supporting_documents = null;
        $newVoucher->save();

        return redirect()->route('vouchers.show', $newVoucher)
            ->with('success', 'Voucher duplicated successfully.');
    }
}