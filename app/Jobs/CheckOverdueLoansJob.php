<?php

namespace App\Jobs;

use App\Models\Loan;
use App\Models\LoanRepayment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckOverdueLoansJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $overdueRepayments = LoanRepayment::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->with(['loan.loanProduct', 'loan.member.user'])
            ->get();

        foreach ($overdueRepayments as $repayment) {
            $daysLate = Carbon::now()->diffInDays($repayment->due_date);
            $penaltyRate = $repayment->loan->loanProduct->penalty_rate / 100;
            $penaltyAmount = $repayment->outstanding_amount * $penaltyRate * $daysLate;

            // Update repayment with penalty
            $repayment->update([
                'status' => 'overdue',
                'penalty_amount' => $penaltyAmount,
                'days_late' => $daysLate,
                'outstanding_amount' => $repayment->expected_amount + $penaltyAmount
            ]);

            // Update loan arrears
            $loan = $repayment->loan;
            $loan->update([
                'days_in_arrears' => $daysLate,
                'penalty_balance' => $loan->penalty_balance + $penaltyAmount,
                'outstanding_balance' => $loan->outstanding_balance + $penaltyAmount
            ]);

            // Send overdue notification
            if ($daysLate % 7 == 0) { // Weekly reminders
                SendNotificationJob::dispatch(
                    new \App\Models\Notification([
                        'user_id' => $loan->member->user_id,
                        'title' => 'Overdue Loan Payment',
                        'message' => "Your loan payment of KES " . number_format($repayment->expected_amount, 2) . " is {$daysLate} days overdue. Please make payment to avoid additional penalties.",
                        'type' => 'loan',
                        'channel' => 'sms'
                    ]),
                    $loan->member->user
                );
            }
        }
    }
}