<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Member;
use App\Models\Transaction;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\ShareTransferRequest;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf;


class AccountController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index(Request $request): Response
    {
        $query = Account::with(['member.user', 'transactions' => function ($q) {
            $q->latest()->limit(3);
        }])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('account_number', 'like', "%{$search}%")
                      ->orWhereHas('member', function ($memberQuery) use ($search) {
                          $memberQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('membership_id', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->account_type, function ($query, $type) {
                $query->where('account_type', $type);
            })
            ->when($request->status, function ($query, $status) {
                $active = $status === 'active';
                $query->where('is_active', $active);
            })
            ->when($request->sortBy, function ($query, $sortBy) use ($request) {
                $direction = $request->sortDirection ?? 'desc';
                $query->orderBy($sortBy, $direction);
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            });

        $accounts = $query->paginate(20)->withQueryString();

        return Inertia::render('Accounts/Index', [
            'accounts' => $accounts,
            'filters' => $request->only(['search', 'account_type', 'status', 'sortBy', 'sortDirection']),
            'stats' => [
                'total_accounts' => Account::count(),
                'active_accounts' => Account::where('is_active', true)->count(),
                'total_balance' => Account::where('is_active', true)->sum('balance'),
                'share_capital_balance' => Account::where('account_type', 'share_capital')->sum('balance'),
                'share_deposits_balance' => Account::where('account_type', 'share_deposits')->sum('balance'),
            ],
            'accountTypes' => [
                'share_capital' => 'Share Capital',
                'share_deposits' => 'Share Deposits'
            ]
        ]);
    }

    /**
     * Show the form for creating a new account
     */
    public function create(): Response
    {
        return Inertia::render('Accounts/Create', [
            'members' => Member::with('user')
                ->where('membership_status', 'active')
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->first_name . ' ' . $member->last_name,
                        'membership_id' => $member->membership_id,
                        'email' => $member->user->email,
                    ];
                }),
            'accountTypes' => [
                'share_capital' => 'Share Capital',
                'share_deposits' => 'Share Deposits'
            ]
        ]);
    }

    /**
     * Store a newly created account
     */
    public function store(StoreAccountRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Check if member already has this account type
            $existingAccount = Account::where('member_id', $request->member_id)
                ->where('account_type', $request->account_type)
                ->first();

            if ($existingAccount) {
                return back()->withErrors(['account_type' => 'Member already has this account type']);
            }

            $account = Account::create([
                'member_id' => $request->member_id,
                'account_number' => $this->generateAccountNumber($request->account_type),
                'account_type' => $request->account_type,
                'balance' => 0,
                'available_balance' => 0,
                'is_active' => true,
            ]);

            // Create initial transaction record
            Transaction::create([
                'transaction_id' => $this->generateTransactionId(),
                'account_id' => $account->id,
                'member_id' => $request->member_id,
                'transaction_type' => 'account_opening',
                'amount' => 0,
                'balance_before' => 0,
                'balance_after' => 0,
                'description' => "Account opening - {$account->account_type} account",
                'status' => 'completed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('accounts.show', $account)
                ->with('success', 'Account created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create account: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified account
     */
    public function show(Account $account): Response
    {
        $account->load([
            'member.user',
            'transactions' => function ($query) {
                $query->with('processedBy')->latest()->limit(20);
            }
        ]);

        return Inertia::render('Accounts/Show', [
            'account' => $account,
            'recentTransactions' => $account->transactions,
            'stats' => [
                'total_deposits' => $account->transactions()
                    ->whereIn('transaction_type', ['deposit', 'share_purchase', 'share_transfer_in'])
                    ->where('status', 'completed')
                    ->sum('amount'),
                'total_withdrawals' => $account->transactions()
                    ->whereIn('transaction_type', ['withdrawal', 'share_sale', 'share_transfer_out'])
                    ->where('status', 'completed')
                    ->sum('amount'),
                'transaction_count' => $account->transactions()->count(),
                'last_transaction' => $account->last_transaction_at,
            ]
        ]);
    }

    /**
     * Show the form for editing the specified account
     */
    public function edit(Account $account): Response
    {
        $account->load('member.user');

        return Inertia::render('Accounts/Edit', [
            'account' => $account,
            'accountTypes' => [
                'share_capital' => 'Share Capital',
                'share_deposits' => 'Share Deposits'
            ]
        ]);
    }

    /**
     * Update the specified account
     */
    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        try {
            $account->update($request->validated());

            return redirect()->route('accounts.show', $account)
                ->with('success', 'Account updated successfully');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update account: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified account
     */
    public function destroy(Account $account): RedirectResponse
    {
        try {
            // Check if account has balance
            if ($account->balance > 0) {
                return back()->withErrors(['error' => 'Cannot delete account with balance']);
            }

            // Check if account has pending transactions
            if ($account->transactions()->where('status', 'pending')->exists()) {
                return back()->withErrors(['error' => 'Cannot delete account with pending transactions']);
            }

            DB::beginTransaction();

            // Create account closure transaction
            Transaction::create([
                'transaction_id' => $this->generateTransactionId(),
                'account_id' => $account->id,
                'member_id' => $account->member_id,
                'transaction_type' => 'account_closure',
                'amount' => 0,
                'balance_before' => 0,
                'balance_after' => 0,
                'description' => "Account closure - {$account->account_type} account",
                'status' => 'completed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Deactivate account instead of deleting
            $account->update(['is_active' => false]);

            DB::commit();

            return redirect()->route('accounts.index')
                ->with('success', 'Account closed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to close account: ' . $e->getMessage()]);
        }
    }

    /**
     * Show deposit form
     */
    public function showDeposit(Account $account): Response
    {
        return Inertia::render('Accounts/Deposit', [
            'account' => $account->load('member.user'),
            'paymentMethods' => [
                'cash' => 'Cash',
                'mobile_money' => 'Mobile Money',
                'bank_transfer' => 'Bank Transfer',
                'cheque' => 'Cheque'
            ]
        ]);
    }

    /**
     * Process deposit
     */
    public function deposit(DepositRequest $request, Account $account): RedirectResponse
    {
        try {
            // Only allow deposits to share_deposits account
            if ($account->account_type !== 'share_deposits') {
                return back()->withErrors(['error' => 'Deposits can only be made to Share Deposits account']);
            }

            DB::beginTransaction();

            $amount = $request->amount;
            $balanceBefore = $account->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => $this->generateTransactionId(),
                'account_id' => $account->id,
                'member_id' => $account->member_id,
                'transaction_type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $request->description ?? "Monthly share deposit",
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'status' => 'completed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Update account balance
            $account->update([
                'balance' => $balanceAfter,
                'available_balance' => $balanceAfter,
                'last_transaction_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('accounts.show', $account)
                ->with('success', 'Deposit processed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process deposit: ' . $e->getMessage()]);
        }
    }

    /**
     * Show withdrawal form
     */
    public function showWithdrawal(Account $account): Response
    {
        return Inertia::render('Accounts/Withdrawal', [
            'account' => $account->load('member.user'),
            'paymentMethods' => [
                'cash' => 'Cash',
                'mobile_money' => 'Mobile Money',
                'bank_transfer' => 'Bank Transfer',
                'cheque' => 'Cheque'
            ]
        ]);
    }

    /**
     * Process withdrawal
     */
    public function withdrawal(WithdrawalRequest $request, Account $account): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $amount = $request->amount;
            $balanceBefore = $account->balance;

            // Check if sufficient balance
            if ($balanceBefore < $amount) {
                return back()->withErrors(['amount' => 'Insufficient balance']);
            }

            $balanceAfter = $balanceBefore - $amount;

            // Determine transaction type based on account type
            $transactionType = $account->account_type === 'share_capital' ? 'share_sale' : 'withdrawal';

            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => $this->generateTransactionId(),
                'account_id' => $account->id,
                'member_id' => $account->member_id,
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $request->description ?? "Withdrawal from {$account->account_type} account",
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'status' => 'completed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Update account balance
            $account->update([
                'balance' => $balanceAfter,
                'available_balance' => $balanceAfter,
                'last_transaction_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('accounts.show', $account)
                ->with('success', 'Withdrawal processed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process withdrawal: ' . $e->getMessage()]);
        }
    }

    /**
     * Show share transfer form (for share capital only)
     */
    public function showShareTransfer(Account $account): Response
    {
        if ($account->account_type !== 'share_capital') {
            return redirect()->route('accounts.show', $account)
                ->with('error', 'Share transfer is only available for Share Capital accounts');
        }

        return Inertia::render('Accounts/ShareTransfer', [
            'account' => $account->load('member.user'),
            'members' => Member::with('user')
                ->where('membership_status', 'active')
                ->where('id', '!=', $account->member_id)
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->first_name . ' ' . $member->last_name,
                        'membership_id' => $member->membership_id,
                    ];
                })
        ]);
    }

    /**
     * Process share transfer (for exiting members)
     */
    public function shareTransfer(ShareTransferRequest $request, Account $account): RedirectResponse
    {
        try {
            if ($account->account_type !== 'share_capital') {
                return back()->withErrors(['error' => 'Share transfer is only available for Share Capital accounts']);
            }

            DB::beginTransaction();

            $amount = $request->amount;
            $recipientMember = Member::findOrFail($request->recipient_member_id);

            // Check if sufficient balance
            if ($account->balance < $amount) {
                return back()->withErrors(['amount' => 'Insufficient balance']);
            }

            // Find or create recipient's share capital account
            $recipientAccount = Account::firstOrCreate([
                'member_id' => $recipientMember->id,
                'account_type' => 'share_capital',
            ], [
                'account_number' => $this->generateAccountNumber('share_capital'),
                'balance' => 0,
                'available_balance' => 0,
                'is_active' => true,
            ]);

            // Process sender's transaction
            $senderBalanceBefore = $account->balance;
            $senderBalanceAfter = $senderBalanceBefore - $amount;

            Transaction::create([
                'transaction_id' => $this->generateTransactionId(),
                'account_id' => $account->id,
                'member_id' => $account->member_id,
                'transaction_type' => 'share_transfer_out',
                'amount' => $amount,
                'balance_before' => $senderBalanceBefore,
                'balance_after' => $senderBalanceAfter,
                'description' => "Share capital transfer to {$recipientMember->first_name} {$recipientMember->last_name} - Member exiting",
                'payment_method' => 'system_transfer',
                'payment_reference' => "TRANSFER-{$recipientMember->membership_id}",
                'status' => 'completed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Update sender's account
            $account->update([
                'balance' => $senderBalanceAfter,
                'available_balance' => $senderBalanceAfter,
                'last_transaction_at' => now(),
            ]);

            // Process recipient's transaction
            $recipientBalanceBefore = $recipientAccount->balance;
            $recipientBalanceAfter = $recipientBalanceBefore + $amount;

            Transaction::create([
                'transaction_id' => $this->generateTransactionId(),
                'account_id' => $recipientAccount->id,
                'member_id' => $recipientMember->id,
                'transaction_type' => 'share_purchase',
                'amount' => $amount,
                'balance_before' => $recipientBalanceBefore,
                'balance_after' => $recipientBalanceAfter,
                'description' => "Share capital purchase from exiting member {$account->member->first_name} {$account->member->last_name}",
                'payment_method' => 'system_transfer',
                'payment_reference' => "TRANSFER-{$account->member->membership_id}",
                'status' => 'completed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Update recipient's account
            $recipientAccount->update([
                'balance' => $recipientBalanceAfter,
                'available_balance' => $recipientBalanceAfter,
                'last_transaction_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('accounts.show', $account)
                ->with('success', 'Share capital transfer completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process share transfer: ' . $e->getMessage()]);
        }
    }

    // Account's statement
    public function statement($id)
    {
        $account = Account::with(['member', 'transactions' => function($q) {
            $q->orderBy('date', 'desc');
        }])->findOrFail($id);

        return Inertia::render('Accounts/Statement', [
            'account' => $account,
            'transactions' => $account->transactions
        ]);
    }

    // Individual Account's statement
    public function myStatement($memberId, $accountId)
    {
        $memberIdLoggedIn = Auth::user()->member->id ?? null;

        if ($memberIdLoggedIn != $memberId) {
            abort(403); 
        }

        // Default period: last 30 days
        $from = request()->query('from') ?? now()->subDays(30)->toDateString();
        $to = request()->query('to') ?? now()->toDateString();

        $account = Account::where('id', $accountId)
            ->where('member_id', $memberId)
            ->with(['member', 'transactions' => function($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->orderBy('created_at', 'desc'); 
            }])
            ->firstOrFail();

        return Inertia::render('Accounts/Statement', [
            'account' => $account,
            'transactions' => $account->transactions,
            'period' => [
                'from' => $from,
                'to' => $to,
            ]
        ]);
    }

    public function statementPdf(Request $request, $accountId)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $memberId = Auth::user()->member->id ?? null;

        $account = Account::where('id', $accountId)
            ->where('member_id', $memberId)
            ->with(['member', 'transactions' => function($q) use ($request) {
                if ($request->from && $request->to) {
                    $q->whereBetween('created_at', [
                        $request->from . ' 00:00:00',
                        $request->to . ' 23:59:59'
                    ]);
                }
                $q->orderBy('created_at', 'desc');
            }])
            ->firstOrFail();

        $from = $request->from ?? $account->transactions->last()?->created_at->toDateString() ?? now()->toDateString();
        $to = $request->to ?? $account->transactions->first()?->created_at->toDateString() ?? now()->toDateString();

        $pdf = Pdf::loadView('accounts.pdf.statement', [
            'account' => $account,
            'transactions' => $account->transactions,
            'period' => ['from' => $from, 'to' => $to],
        ])->setPaper('A4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true);

        return $pdf->stream("statement-{$account->account_number}.pdf");
    }

    /**
     * Get account transactions
     */
    public function transactions(Account $account, Request $request): Response
    {
        $query = $account->transactions()
            ->with('processedBy')
            ->when($request->transaction_type, function ($query, $type) {
                $query->where('transaction_type', $type);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->latest();

        $transactions = $query->paginate(20)->withQueryString();

        return Inertia::render('Accounts/Transactions', [
            'account' => $account->load('member.user'),
            'transactions' => $transactions,
            'filters' => $request->only(['transaction_type', 'status', 'date_from', 'date_to']),
            'transactionTypes' => [
                'deposit' => 'Deposit',
                'withdrawal' => 'Withdrawal',
                'share_purchase' => 'Share Purchase',
                'share_sale' => 'Share Sale',
                'share_transfer_in' => 'Share Transfer In',
                'share_transfer_out' => 'Share Transfer Out',
                'loan_disbursement' => 'Loan Disbursement',
                'loan_repayment' => 'Loan Repayment',
                'dividend_payment' => 'Dividend Payment',
                'account_opening' => 'Account Opening',
                'account_closure' => 'Account Closure',
            ],
            'statuses' => [
                'pending' => 'Pending',
                'completed' => 'Completed',
                'failed' => 'Failed',
                'reversed' => 'Reversed',
            ]
        ]);
    }

    /**
     * Generate account number
     */
    private function generateAccountNumber(string $accountType): string
    {
        $prefix = match ($accountType) {
            'share_capital' => 'SHC',
            'share_deposits' => 'SHD',
            default => 'ACC'
        };

        do {
            $number = $prefix . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        } while (Account::where('account_number', $number)->exists());

        return $number;
    }

    /**
     * Generate transaction ID
     */
    private function generateTransactionId(): string
    {
        do {
            $id = 'TXN' . date('Ymd') . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (Transaction::where('transaction_id', $id)->exists());

        return $id;
    }
}