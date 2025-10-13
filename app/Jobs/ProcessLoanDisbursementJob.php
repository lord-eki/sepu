<?php

namespace App\Jobs;

use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\PaymentVoucher;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessLoanDisbursementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $loan;
    protected $disbursedBy;

    public function __construct(Loan $loan, $disbursedBy)
    {
        $this->loan = $loan;
        $this->disbursedBy = $disbursedBy;
    }

    public function handle(PaymentService $paymentService)
    {
        DB::transaction(function () use ($paymentService) {
            // Get member's savings account
            $account = Account::where('member_id', $this->loan->member_id)
                ->where('account_type', 'savings')
                ->first();

            if (!$account) {
                throw new \Exception('Member savings account not found');
            }

            // Create disbursement transaction
            $transaction = Transaction::create([
                'transaction_id' => 'TXN-' . time() . '-' . rand(1000, 9999),
                'account_id' => $account->id,
                'member_id' => $this->loan->member_id,
                'transaction_type' => 'loan_disbursement',
                'amount' => $this->loan->approved_amount,
                'balance_before' => $account->balance,
                'balance_after' => $account->balance + $this->loan->approved_amount,
                'description' => 'Loan disbursement for loan #' . $this->loan->loan_number,
                'reference_number' => $this->loan->loan_number,
                'payment_method' => 'system_transfer',
                'status' => 'completed',
                'processed_by' => $this->disbursedBy,
                'processed_at' => now(),
                'metadata' => json_encode(['loan_id' => $this->loan->id])
            ]);

            // Update account balance
            $account->update([
                'balance' => $account->balance + $this->loan->approved_amount,
                'available_balance' => $account->available_balance + $this->loan->approved_amount,
                'last_transaction_at' => now()
            ]);

            // Update loan status
            $this->loan->update([
                'status' => 'disbursed',
                'disbursed_amount' => $this->loan->approved_amount,
                'disbursement_date' => now(),
                'disbursed_by' => $this->disbursedBy,
                'outstanding_balance' => $this->loan->total_repayable,
                'principal_balance' => $this->loan->approved_amount
            ]);

            // Create payment voucher
            PaymentVoucher::create([
                'voucher_number' => 'PV-' . time() . '-' . rand(1000, 9999),
                'voucher_type' => 'loan_disbursement',
                'payee_name' => $this->loan->member->first_name . ' ' . $this->loan->member->last_name,
                'payee_phone' => $this->loan->member->user->phone,
                'amount' => $this->loan->approved_amount,
                'purpose' => 'Loan disbursement',
                'description' => 'Disbursement for loan #' . $this->loan->loan_number,
                'loan_id' => $this->loan->id,
                'status' => 'paid',
                'created_by' => $this->disbursedBy,
                'approved_by' => $this->disbursedBy,
                'paid_by' => $this->disbursedBy,
                'approval_date' => now(),
                'payment_date' => now()
            ]);

            // Schedule loan repayment generation
            GenerateLoanRepaymentsJob::dispatch($this->loan);

            // Send notification
            SendNotificationJob::dispatch(
                new \App\Models\Notification([
                    'user_id' => $this->loan->member->user_id,
                    'title' => 'Loan Disbursed',
                    'message' => "Your loan of KES " . number_format($this->loan->approved_amount, 2) . " has been disbursed successfully.",
                    'type' => 'loan',
                    'channel' => 'sms'
                ]),
                $this->loan->member->user
            );
        });
    }

    public function failed(\Exception $exception)
    {
        Log::error('Loan disbursement failed: ' . $exception->getMessage());
        $this->loan->update(['status' => 'approved']); // Revert status
    }
}
