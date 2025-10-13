<?php
namespace App\Jobs;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Member;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessDepositJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transactionData;
    protected $memberId;

    public function __construct(array $transactionData, $memberId)
    {
        $this->transactionData = $transactionData;
        $this->memberId = $memberId;
    }

    public function handle(PaymentService $paymentService)
    {
        DB::transaction(function () use ($paymentService) {
            $member = Member::findOrFail($this->memberId);
            $account = Account::where('member_id', $this->memberId)
                ->where('account_type', $this->transactionData['account_type'])
                ->first();

            if (!$account) {
                throw new \Exception('Account not found for member');
            }

            // Verify payment with gateway
            $paymentVerified = $paymentService->verifyPayment(
                $this->transactionData['payment_reference'],
                $this->transactionData['amount']
            );

            if (!$paymentVerified) {
                throw new \Exception('Payment verification failed');
            }

            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => 'TXN-' . time() . '-' . rand(1000, 9999),
                'account_id' => $account->id,
                'member_id' => $this->memberId,
                'transaction_type' => 'deposit',
                'amount' => $this->transactionData['amount'],
                'balance_before' => $account->balance,
                'balance_after' => $account->balance + $this->transactionData['amount'],
                'description' => $this->transactionData['description'] ?? 'Member deposit',
                'reference_number' => $this->transactionData['reference_number'] ?? null,
                'payment_method' => $this->transactionData['payment_method'],
                'payment_reference' => $this->transactionData['payment_reference'],
                'status' => 'completed',
                'processed_by' => $this->transactionData['processed_by'] ?? null,
                'processed_at' => now()
            ]);

            // Update account balance
            $account->update([
                'balance' => $account->balance + $this->transactionData['amount'],
                'available_balance' => $account->available_balance + $this->transactionData['amount'],
                'last_transaction_at' => now()
            ]);

            // Send confirmation notification
            SendNotificationJob::dispatch(
                new \App\Models\Notification([
                    'user_id' => $member->user_id,
                    'title' => 'Deposit Successful',
                    'message' => "Your deposit of KES " . number_format($this->transactionData['amount'], 2) . " has been processed successfully. New balance: KES " . number_format($account->balance, 2),
                    'type' => 'transaction',
                    'channel' => 'sms'
                ]),
                $member->user
            );
        });
    }
}