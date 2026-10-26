<?php

namespace App\Services\Loans;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\PaymentVoucher;
use App\Models\User;
use App\Services\Accounting\AccountBalanceService;
use App\Services\Accounting\LedgerService;
use App\Services\LoanEligibilityService;
use DomainException;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(
        private LoanCalculator $calculator,
        private AccountBalanceService $balances,
        private LedgerService $ledger,
        private LoanEligibilityService $eligibility,
    ) {
    }

    //  Approval 

    public function approve(int $loanId, float $approvedAmount, User $approver, ?string $notes = null): Loan
    {
        return DB::transaction(function () use ($loanId, $approvedAmount, $approver, $notes) {
            $loan = Loan::with(['member.user', 'loanProduct'])->whereKey($loanId)->lockForUpdate()->firstOrFail();

            if ($loan->status !== 'pending') {
                throw new DomainException('Only pending loans can be approved.');
            }

            $member  = $loan->member;
            $product = $loan->loanProduct;
            $amount  = round($approvedAmount, 2);

            if ((int) $member->user_id === (int) $approver->id) {
                throw new DomainException('You cannot approve your own loan.');
            }
            if ($amount <= 0) {
                throw new DomainException('Approved amount must be greater than zero.');
            }
            if ($amount > round((float) $loan->applied_amount, 2)) {
                throw new DomainException('Approved amount cannot exceed the amount applied for (KES ' . number_format($loan->applied_amount, 2) . ').');
            }
            if ($amount < (float) $product->min_amount || $amount > (float) $product->max_amount) {
                throw new DomainException(
                    'Approved amount must be between KES ' . number_format($product->min_amount, 2) .
                    ' and KES ' . number_format($product->max_amount, 2) . ' for this product.'
                );
            }

            if ($product->requires_guarantor || $loan->guarantors()->exists()) {
                $accepted = (float) $loan->guarantors()->where('status', 'accepted')->sum('guaranteed_amount');

                if ($accepted + 0.005 < $amount) {
                    throw new DomainException(
                        'Accepted guarantees (KES ' . number_format($accepted, 2) . ') do not cover the approved amount.'
                    );
                }
            }

            $check = $this->eligibility->checkEligibility($member, $product, $amount);
            if (!$check['eligible']) {
                $reasons = collect($check['messages'])->flatten()->filter()->implode(' ');
                throw new DomainException('Member is not eligible for this amount. ' . $reasons);
            }

            $calc = $this->calculator->summary(
                $amount,
                (float) $loan->interest_rate,
                (int) $loan->term_months,
                (float) $product->processing_fee_rate,
                (float) $product->insurance_rate,
            );

            $old = $loan->toArray();

            $loan->transitionTo('approved', [
                'approved_amount'   => $amount,
                'approval_date'     => now()->toDateString(),
                'approved_by'       => $approver->id,
                'approval_notes'    => $notes,
                'monthly_repayment' => $calc['monthly_payment'],
                'total_repayable'   => $calc['total_repayable'],
                'processing_fee'    => $calc['processing_fee'],
                'insurance_fee'     => $calc['insurance_fee'],
            ]);

            $this->audit($approver, 'loan_approved', $loan, $old);

            return $loan->fresh(['member', 'loanProduct']);
        });
    }

    //  Disbursement 


    public function disburse(int $loanId, string $method, ?string $reference, User $by): array
    {
        if (!in_array($method, ['cash', 'mobile_money', 'bank_transfer', 'account'], true)) {
            throw new DomainException('Invalid disbursement method.');
        }
        if (in_array($method, ['mobile_money', 'bank_transfer'], true) && blank($reference)) {
            throw new DomainException('A payment reference is required for mobile money and bank transfer disbursements.');
        }

        return DB::transaction(function () use ($loanId, $method, $reference, $by) {
            $loan = Loan::with(['member.user', 'loanProduct'])->whereKey($loanId)->lockForUpdate()->firstOrFail();

            if ($loan->status !== 'approved') {
                throw new DomainException('Only approved loans can be disbursed.');
            }
            if ((int) $loan->member->user_id === (int) $by->id) {
                throw new DomainException('You cannot disburse your own loan.');
            }
            if (config('sepu.maker_checker') && $loan->approved_by && (int) $loan->approved_by === (int) $by->id) {
                throw new DomainException('The person who approved a loan cannot also disburse it. Another authorised user must do this.');
            }

            $gross         = round((float) $loan->approved_amount, 2);
            $processingFee = round((float) $loan->processing_fee, 2);
            $insuranceFee  = round((float) $loan->insurance_fee, 2);
            $net           = round($gross - $processingFee - $insuranceFee, 2);

            if ($net <= 0) {
                throw new DomainException('Net disbursement cannot be zero or negative.');
            }

            $now      = now();
            $graceDays = (int) ($loan->loanProduct->grace_period_days ?? 0);
            $firstDue = $now->copy()->addDays($graceDays)->addMonthNoOverflow();

            $calc = $this->calculator->summary($gross, (float) $loan->interest_rate, (int) $loan->term_months, 0, 0, $firstDue);
            $schedule = $calc['schedule'];

            $old = $loan->toArray();

            // outstanding principal goes up by the gross amount
            $loanAccount = $this->loanAccountFor($loan->member_id);

            $transaction = $this->balances->credit($loanAccount->id, $gross, [
                'transaction_type'  => 'loan_disbursement',
                'description'       => "Loan disbursement for loan {$loan->loan_number}",
                'reference_number'  => $loan->loan_number,
                'payment_method'    => $method === 'account' ? 'system_transfer' : $method,
                'payment_reference' => $reference,
                'processed_by'      => $by->id,
                'created_by'        => $by->id,
                'approved_by'       => $loan->approved_by,
                'idempotency_key'   => "loan:{$loan->id}:disbursement",
                'metadata'          => [
                    'loan_id'              => $loan->id,
                    'loan_number'          => $loan->loan_number,
                    'gross_amount'         => $gross,
                    'processing_fee'       => $processingFee,
                    'insurance_fee'        => $insuranceFee,
                    'net_amount'           => $net,
                    'disbursement_method'  => $method,
                ],
            ]);

            // Net proceeds to the member's deposits account
            if ($method === 'account') {
                $deposits = Account::where('member_id', $loan->member_id)
                    ->where('account_type', 'share_deposits')
                    ->where('is_active', true)
                    ->first();

                if (!$deposits) {
                    throw new DomainException('Member has no active share deposits account to receive the proceeds.');
                }

                $this->balances->credit($deposits->id, $net, [
                    'transaction_type' => 'loan_disbursement',
                    'description'      => "Loan proceeds for loan {$loan->loan_number}",
                    'reference_number' => $loan->loan_number,
                    'payment_method'   => 'system_transfer',
                    'processed_by'     => $by->id,
                    'created_by'       => $by->id,
                    'idempotency_key'  => "loan:{$loan->id}:proceeds",
                    'metadata'         => ['loan_id' => $loan->id, 'net_amount' => $net],
                ]);
            }

            // Double entry: Dr loans (gross) / Cr cash-or-bank-or-member-deposits (net) / Cr fee income / Cr insurance payable
            $payoutRole = match ($method) {
                'cash'                          => 'cash',
                'mobile_money', 'bank_transfer' => 'bank',
                'account'                       => 'member_deposits',
            };

            $desc  = "Loan disbursement {$loan->loan_number}";
            $lines = [
                ['account' => 'loans_performing', 'type' => 'debit',  'amount' => $gross, 'description' => $desc],
                ['account' => $payoutRole,        'type' => 'credit', 'amount' => $net,   'description' => $desc],
            ];
            if ($processingFee > 0) {
                $lines[] = ['account' => 'processing_fee_income', 'type' => 'credit', 'amount' => $processingFee, 'description' => "Processing fee {$loan->loan_number}"];
            }
            if ($insuranceFee > 0) {
                $lines[] = ['account' => 'insurance_payable', 'type' => 'credit', 'amount' => $insuranceFee, 'description' => "Insurance fee {$loan->loan_number}"];
            }

            $entry = $this->ledger->post($lines, $desc, $loan, "loan:{$loan->id}:disbursement", $by->id, $now->toDateString());
            $transaction->update(['journal_entry_id' => $entry->id]);

            // Repayment schedule 
            LoanRepayment::where('loan_id', $loan->id)->delete();

            foreach ($schedule as $row) {
                LoanRepayment::create([
                    'loan_id'            => $loan->id,
                    'transaction_id'     => null,
                    'due_date'           => $row['payment_date'],
                    'expected_amount'    => $row['payment_amount'],
                    'principal_amount'   => $row['principal_amount'],
                    'interest_amount'    => $row['interest_amount'],
                    'penalty_amount'     => 0,
                    'paid_amount'        => 0,
                    'outstanding_amount' => $row['payment_amount'],
                    'status'             => 'pending',
                    'payment_date'       => null,
                    'days_late'          => 0,
                ]);
            }

            // Loan becomes active 
            $loan->transitionTo('active', [
                'disbursed_amount'     => $net,
                'disbursed_by'         => $by->id,
                'disbursement_date'    => $now->toDateString(),
                'first_repayment_date' => $schedule[0]['payment_date'],
                'maturity_date'        => end($schedule)['payment_date'],
                'monthly_repayment'    => $calc['monthly_payment'],
                'total_repayable'      => $calc['total_repayable'],
                'principal_balance'    => $gross,
                'interest_balance'     => $calc['total_interest'],
                'penalty_balance'      => 0,
                'outstanding_balance'  => $calc['total_repayable'],
                'days_in_arrears'      => 0,
            ]);

            //  Payment voucher 
            $member = $loan->member;
            PaymentVoucher::create([
                'voucher_number' => 'VOU' . date('YmdHis') . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
                'voucher_type'   => 'loan_disbursement',
                'payee_name'     => trim($member->first_name . ' ' . $member->last_name),
                'payee_phone'    => $member->user->phone ?? null,
                'amount'         => $net,
                'purpose'        => 'Loan disbursement',
                'description'    => "Disbursement for loan {$loan->loan_number} via {$method}" . ($reference ? " ({$reference})" : ''),
                'loan_id'        => $loan->id,
                'status'         => 'paid',
                'created_by'     => $by->id,
                'approved_by'    => $loan->approved_by ?? $by->id,
                'paid_by'        => $by->id,
                'approval_date'  => $now,
                'payment_date'   => $now,
            ]);

            $this->audit($by, 'loan_disbursed', $loan, $old);

            return [
                'loan'             => $loan->fresh(['member', 'loanProduct']),
                'transaction'      => $transaction,
                'net_disbursement' => $net,
            ];
        });
    }

    //  Helpers 

    private function loanAccountFor(int $memberId): Account
    {
        $account = Account::where('member_id', $memberId)->where('account_type', 'loan_outstanding')->first();

        if ($account) {
            return $account;
        }

        do {
            $number = '01' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Account::where('account_number', $number)->exists());

        return Account::create([
            'member_id'         => $memberId,
            'account_number'    => $number,
            'account_type'      => 'loan_outstanding',
            'balance'           => 0,
            'available_balance' => 0,
            'is_active'         => true,
        ]);
    }

    private function audit(User $user, string $action, Loan $loan, array $old): void
    {
        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => $action,
            'model_type' => Loan::class,
            'model_id'   => $loan->id,
            'old_values' => $old,
            'new_values' => $loan->fresh()->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}