<?php

namespace App\Jobs;

use App\Models\Dividend;
use App\Models\MemberDividend;
use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessDividendDistributionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dividend;
    protected $distributedBy;

    public function __construct(Dividend $dividend, $distributedBy)
    {
        $this->dividend = $dividend;
        $this->distributedBy = $distributedBy;
    }

    public function handle()
    {
        DB::transaction(function () {
            $memberDividends = MemberDividend::where('dividend_id', $this->dividend->id)
                ->where('status', 'pending')
                ->with('member.accounts')
                ->get();

            foreach ($memberDividends as $memberDividend) {
                $savingsAccount = $memberDividend->member->accounts
                    ->where('account_type', 'savings')
                    ->first();

                if ($savingsAccount) {
                    // Create dividend transaction
                    $transaction = Transaction::create([
                        'transaction_id' => 'TXN-' . time() . '-' . rand(1000, 9999),
                        'account_id' => $savingsAccount->id,
                        'member_id' => $memberDividend->member_id,
                        'transaction_type' => 'dividend_payment',
                        'amount' => $memberDividend->dividend_amount,
                        'balance_before' => $savingsAccount->balance,
                        'balance_after' => $savingsAccount->balance + $memberDividend->dividend_amount,
                        'description' => 'Dividend payment for year ' . $this->dividend->dividend_year,
                        'reference_number' => 'DIV-' . $this->dividend->dividend_year . '-' . $memberDividend->member_id,
                        'payment_method' => 'system_transfer',
                        'status' => 'completed',
                        'processed_by' => $this->distributedBy,
                        'processed_at' => now(),
                        'metadata' => json_encode(['dividend_id' => $this->dividend->id])
                    ]);

                    // Update account balance
                    $savingsAccount->update([
                        'balance' => $savingsAccount->balance + $memberDividend->dividend_amount,
                        'available_balance' => $savingsAccount->available_balance + $memberDividend->dividend_amount,
                        'last_transaction_at' => now()
                    ]);

                    // Update member dividend status
                    $memberDividend->update([
                        'status' => 'paid',
                        'payment_date' => now(),
                        'transaction_id' => $transaction->id
                    ]);

                    // Send notification
                    SendNotificationJob::dispatch(
                        new \App\Models\Notification([
                            'user_id' => $memberDividend->member->user_id,
                            'title' => 'Dividend Payment',
                            'message' => "Your dividend of KES " . number_format($memberDividend->dividend_amount, 2) . " for year {$this->dividend->dividend_year} has been credited to your account.",
                            'type' => 'dividend',
                            'channel' => 'sms'
                        ]),
                        $memberDividend->member->user
                    );
                }
            }

            // Update dividend status
            $this->dividend->update([
                'status' => 'distributed',
                'distribution_date' => now()
            ]);
        });
    }
}