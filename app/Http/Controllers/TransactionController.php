<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Member;
use App\Models\User;
use App\Models\LoanRepayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['account', 'member', 'processedBy'])
            ->orderBy('created_at', 'desc');

        // Filter by member if specified
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        // Filter by account if specified
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by transaction type
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by amount range
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('transaction_id', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('reference_number', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('payment_reference', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('member', function ($memberQuery) use ($searchTerm) {
                      $memberQuery->where('first_name', 'LIKE', "%{$searchTerm}%")
                                 ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                                 ->orWhere('membership_id', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        $transactions = $query->paginate(20);

        // Get summary statistics
        $totalTransactions = $query->count();
        $totalAmount = $query->sum('amount');
        $pendingCount = $query->where('status', 'pending')->count();
        $completedCount = $query->where('status', 'completed')->count();

        $statistics = [
            'total_transactions' => $totalTransactions,
            'total_amount' => $totalAmount,
            'pending_count' => $pendingCount,
            'completed_count' => $completedCount,
            'failed_count' => $query->where('status', 'failed')->count(),
            'reversed_count' => $query->where('status', 'reversed')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create()
    {
        $accounts = Account::with('member')->where('is_active', true)->get();
        $members = Member::where('membership_status', 'active')->get();
        $transactionTypes = $this->getTransactionTypes();
        $paymentMethods = $this->getPaymentMethods();

        return response()->json([
            'success' => true,
            'data' => [
                'accounts' => $accounts,
                'members' => $members,
                'transaction_types' => $transactionTypes,
                'payment_methods' => $paymentMethods,
            ]
        ]);
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'transaction_type' => 'required|in:deposit,withdrawal,transfer,loan_disbursement,loan_repayment,dividend_payment,fee_payment,interest_payment',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'payment_method' => 'required|in:cash,mobile_money,bank_transfer,cheque,system_transfer',
            'payment_reference' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
            'destination_account_id' => 'required_if:transaction_type,transfer|exists:accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $account = Account::findOrFail($request->account_id);
            $member = $account->member;

            // Validate account is active
            if (!$account->is_active) {
                throw new Exception('Account is not active');
            }

            // Validate member is active
            if ($member->membership_status !== 'active') {
                throw new Exception('Member account is not active');
            }

            // For withdrawals and transfers, check sufficient balance
            if (in_array($request->transaction_type, ['withdrawal', 'transfer'])) {
                if ($account->available_balance < $request->amount) {
                    throw new Exception('Insufficient account balance');
                }
            }

            // Generate unique transaction ID
            $transactionId = $this->generateTransactionId();

            // Create the transaction
            $transaction = Transaction::create([
                'transaction_id' => $transactionId,
                'account_id' => $account->id,
                'member_id' => $member->id,
                'transaction_type' => $request->transaction_type,
                'amount' => $request->amount,
                'balance_before' => $account->balance,
                'balance_after' => $this->calculateNewBalance($account, $request->transaction_type, $request->amount),
                'description' => $request->description,
                'reference_number' => $request->reference_number,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'status' => 'pending',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'metadata' => $request->metadata,
            ]);

            // Handle transfer transactions
            if ($request->transaction_type === 'transfer') {
                $this->processTransfer($transaction, $request->destination_account_id);
            }

            // Process the transaction
            $this->processTransaction($transaction);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction->load(['account', 'member', 'processedBy'])
            ], 201);

        } catch (Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show($id)
    {
        $transaction = Transaction::with(['account', 'member', 'processedBy', 'loanRepayment'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit($id)
    {
        $transaction = Transaction::with(['account', 'member'])
            ->findOrFail($id);

        // Only allow editing of pending transactions
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending transactions can be edited'
            ], 400);
        }

        $transactionTypes = $this->getTransactionTypes();
        $paymentMethods = $this->getPaymentMethods();

        return response()->json([
            'success' => true,
            'data' => [
                'transaction' => $transaction,
                'transaction_types' => $transactionTypes,
                'payment_methods' => $paymentMethods,
            ]
        ]);
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Only allow updating of pending transactions
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending transactions can be updated'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric|min:0.01',
            'description' => 'sometimes|required|string|max:500',
            'payment_method' => 'sometimes|required|in:cash,mobile_money,bank_transfer,cheque,system_transfer',
            'payment_reference' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // If amount is being updated, recalculate balance_after
            if ($request->has('amount')) {
                $account = $transaction->account;
                $transaction->balance_after = $this->calculateNewBalance(
                    $account, 
                    $transaction->transaction_type, 
                    $request->amount
                );
            }

            $transaction->update($request->only([
                'amount',
                'description',
                'payment_method',
                'payment_reference',
                'reference_number',
                'metadata',
                'balance_after'
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'data' => $transaction->load(['account', 'member', 'processedBy'])
            ]);

        } catch (Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction update failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Only allow deletion of pending transactions
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending transactions can be deleted'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $transaction->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);

        } catch (Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction deletion failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Approve a pending transaction.
     */
    public function approve($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending transactions can be approved'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $this->processTransaction($transaction);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction approved successfully',
                'data' => $transaction->load(['account', 'member', 'processedBy'])
            ]);

        } catch (Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction approval failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reject a pending transaction.
     */
    public function reject(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending transactions can be rejected'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $transaction->update([
                'status' => 'failed',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'rejection_reason' => $request->rejection_reason,
                    'rejected_by' => Auth::id(),
                    'rejected_at' => now(),
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction rejected successfully',
                'data' => $transaction->load(['account', 'member', 'processedBy'])
            ]);

        } catch (Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction rejection failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reverse a completed transaction.
     */
    public function reverse(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Only completed transactions can be reversed'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'reversal_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $account = $transaction->account;

            // Create reversal transaction
            $reversalTransaction = Transaction::create([
                'transaction_id' => $this->generateTransactionId(),
                'account_id' => $account->id,
                'member_id' => $transaction->member_id,
                'transaction_type' => $this->getReversalTransactionType($transaction->transaction_type),
                'amount' => $transaction->amount,
                'balance_before' => $account->balance,
                'balance_after' => $this->calculateReversalBalance($account, $transaction),
                'description' => 'Reversal of transaction: ' . $transaction->transaction_id,
                'reference_number' => $transaction->reference_number,
                'payment_method' => $transaction->payment_method,
                'payment_reference' => $transaction->payment_reference,
                'status' => 'completed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'metadata' => [
                    'original_transaction_id' => $transaction->id,
                    'reversal_reason' => $request->reversal_reason,
                    'reversed_by' => Auth::id(),
                    'reversed_at' => now(),
                ]
            ]);

            // Update account balance
            $account->update([
                'balance' => $reversalTransaction->balance_after,
                'available_balance' => $reversalTransaction->balance_after,
                'last_transaction_at' => now(),
            ]);

            // Mark original transaction as reversed
            $transaction->update([
                'status' => 'reversed',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'reversal_transaction_id' => $reversalTransaction->id,
                    'reversal_reason' => $request->reversal_reason,
                    'reversed_by' => Auth::id(),
                    'reversed_at' => now(),
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction reversed successfully',
                'data' => [
                    'original_transaction' => $transaction->load(['account', 'member', 'processedBy']),
                    'reversal_transaction' => $reversalTransaction->load(['account', 'member', 'processedBy'])
                ]
            ]);

        } catch (Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction reversal failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get member's transaction history.
     */
    public function memberTransactions(Request $request, $memberId)
    {
        $member = Member::findOrFail($memberId);

        $query = Transaction::with(['account', 'processedBy'])
            ->where('member_id', $memberId)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->paginate(20);

        // Get member's account summary
        $accounts = Account::where('member_id', $memberId)->get();
        $totalBalance = $accounts->sum('balance');
        $totalAvailableBalance = $accounts->sum('available_balance');

        return response()->json([
            'success' => true,
            'data' => [
                'member' => $member,
                'transactions' => $transactions,
                'accounts' => $accounts,
                'summary' => [
                    'total_balance' => $totalBalance,
                    'total_available_balance' => $totalAvailableBalance,
                    'total_transactions' => $query->count(),
                    'total_deposits' => $query->where('transaction_type', 'deposit')->sum('amount'),
                    'total_withdrawals' => $query->where('transaction_type', 'withdrawal')->sum('amount'),
                ]
            ]
        ]);
    }

    /**
     * Get account transactions.
     */
    public function accountTransactions(Request $request, $accountId)
    {
        $account = Account::with('member')->findOrFail($accountId);

        $query = Transaction::with(['member', 'processedBy'])
            ->where('account_id', $accountId)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'account' => $account,
                'transactions' => $transactions,
                'summary' => [
                    'current_balance' => $account->balance,
                    'available_balance' => $account->available_balance,
                    'total_transactions' => $query->count(),
                    'total_deposits' => $query->where('transaction_type', 'deposit')->sum('amount'),
                    'total_withdrawals' => $query->where('transaction_type', 'withdrawal')->sum('amount'),
                ]
            ]
        ]);
    }

    /**
     * Get transaction statistics.
     */
    public function statistics(Request $request)
    {
        $query = Transaction::query();

        // Apply date filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $totalTransactions = $query->count();
        $totalAmount = $query->sum('amount');

        $statistics = [
            'total_transactions' => $totalTransactions,
            'total_amount' => $totalAmount,
            'by_status' => [
                'pending' => $query->where('status', 'pending')->count(),
                'completed' => $query->where('status', 'completed')->count(),
                'failed' => $query->where('status', 'failed')->count(),
                'reversed' => $query->where('status', 'reversed')->count(),
            ],
            'by_type' => [
                'deposits' => $query->where('transaction_type', 'deposit')->sum('amount'),
                'withdrawals' => $query->where('transaction_type', 'withdrawal')->sum('amount'),
                'transfers' => $query->where('transaction_type', 'transfer')->sum('amount'),
                'loan_disbursements' => $query->where('transaction_type', 'loan_disbursement')->sum('amount'),
                'loan_repayments' => $query->where('transaction_type', 'loan_repayment')->sum('amount'),
                'dividend_payments' => $query->where('transaction_type', 'dividend_payment')->sum('amount'),
            ],
            'by_payment_method' => [
                'cash' => $query->where('payment_method', 'cash')->sum('amount'),
                'mobile_money' => $query->where('payment_method', 'mobile_money')->sum('amount'),
                'bank_transfer' => $query->where('payment_method', 'bank_transfer')->sum('amount'),
                'cheque' => $query->where('payment_method', 'cheque')->sum('amount'),
                'system_transfer' => $query->where('payment_method', 'system_transfer')->sum('amount'),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Process a transaction.
     */
    private function processTransaction(Transaction $transaction)
    {
        $account = $transaction->account;

        // Update account balance
        $account->update([
            'balance' => $transaction->balance_after,
            'available_balance' => $transaction->balance_after,
            'last_transaction_at' => now(),
        ]);

        // Mark transaction as completed
        $transaction->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        // Create notification 
        // $this->createTransactionNotification($transaction);
    }

    /**
     * Process transfer transaction.
     */
    private function processTransfer(Transaction $sourceTransaction, $destinationAccountId)
    {
        $destinationAccount = Account::findOrFail($destinationAccountId);

        // Create destination transaction
        $destinationTransaction = Transaction::create([
            'transaction_id' => $this->generateTransactionId(),
            'account_id' => $destinationAccount->id,
            'member_id' => $destinationAccount->member_id,
            'transaction_type' => 'deposit',
            'amount' => $sourceTransaction->amount,
            'balance_before' => $destinationAccount->balance,
            'balance_after' => $destinationAccount->balance + $sourceTransaction->amount,
            'description' => 'Transfer from account: ' . $sourceTransaction->account->account_number,
            'reference_number' => $sourceTransaction->reference_number,
            'payment_method' => 'system_transfer',
            'payment_reference' => $sourceTransaction->transaction_id,
            'status' => 'pending',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'metadata' => [
                'source_transaction_id' => $sourceTransaction->id,
                'transfer_type' => 'incoming',
            ]
        ]);

        // Link transactions
        $sourceTransaction->update([
            'metadata' => array_merge($sourceTransaction->metadata ?? [], [
                'destination_transaction_id' => $destinationTransaction->id,
                'destination_account_id' => $destinationAccountId,
                'transfer_type' => 'outgoing',
            ])
        ]);
    }

    /**
     * Calculate new balance after transaction.
     */
    private function calculateNewBalance(Account $account, string $transactionType, float $amount): float
    {
        $currentBalance = $account->balance;

        switch ($transactionType) {
            case 'deposit':
            case 'loan_disbursement':
            case 'dividend_payment':
                return $currentBalance + $amount;
            
            case 'withdrawal':
            case 'transfer':
            case 'loan_repayment':
            case 'fee_payment':
                return $currentBalance - $amount;
            
            default:
                return $currentBalance;
        }
    }

    /**
     * Calculate reversal balance.
     */
    private function calculateReversalBalance(Account $account, Transaction $originalTransaction): float
    {
        $currentBalance = $account->balance;
        $originalType = $originalTransaction->transaction_type;
        $amount = $originalTransaction->amount;

        // Reverse the original transaction effect
        switch ($originalType) {
            case 'deposit':
            case 'loan_disbursement':
            case 'dividend_payment':
                return $currentBalance - $amount;
            
            case 'withdrawal':
            case 'transfer':
            case 'loan_repayment':
            case 'fee_payment':
                return $currentBalance + $amount;
            
            default:
                return $currentBalance;
        }
    }

    /**
     * Get reversal transaction type.
     */
    private function getReversalTransactionType(string $originalType): string
    {
        $reversalMap = [
            'deposit' => 'withdrawal',
            'withdrawal' => 'deposit',
            'transfer' => 'deposit',
            'loan_disbursement' => 'withdrawal',
            'loan_repayment' => 'deposit',
            'dividend_payment' => 'withdrawal',
            'fee_payment' => 'deposit',
            'interest_payment' => 'deposit',
        ];

        return $reversalMap[$originalType] ?? 'withdrawal';
    }

    /**
     * Generate unique transaction ID.
     */
    private function generateTransactionId(): string
    {
        do {
            $transactionId = 'TXN' . date('Ymd') . strtoupper(Str::random(6));
        } while (Transaction::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }

    /**
     * Get available transaction types.
     */
    private function getTransactionTypes(): array
    {
        return [
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'transfer' => 'Transfer',
            'loan_disbursement' => 'Loan Disbursement',
            'loan_repayment' => 'Loan Repayment',
            'dividend_payment' => 'Dividend Payment',
            'fee_payment' => 'Fee Payment',
            'interest_payment' => 'Interest Payment',
        ];
    }

    /**
     * Get available payment methods.
     */
    private function getPaymentMethods(): array
    {
        return [
            'cash' => 'Cash',
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'system_transfer' => 'System Transfer',
        ];
    }
}