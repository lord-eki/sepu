<?php

namespace App\Jobs;

use App\Models\Dividend;
use App\Models\Member;
use App\Models\MemberDividend;
use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessDividendCalculationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dividend;

    public function __construct(Dividend $dividend)
    {
        $this->dividend = $dividend;
    }

    public function handle()
    {
        DB::transaction(function () {
            // Get all active members with shares
            $members = Member::where('membership_status', 'active')
                ->with(['accounts' => function ($query) {
                    $query->where('account_type', 'shares');
                }])
                ->get();

            // Calculate total shares
            $totalShares = $members->sum(function ($member) {
                return $member->accounts->where('account_type', 'shares')->sum('balance');
            });

            if ($totalShares > 0) {
                foreach ($members as $member) {
                    $sharesAccount = $member->accounts->where('account_type', 'shares')->first();
                    $memberShares = $sharesAccount ? $sharesAccount->balance : 0;

                    if ($memberShares > 0) {
                        $dividendAmount = ($memberShares / $totalShares) * $this->dividend->total_dividends;

                        MemberDividend::create([
                            'dividend_id' => $this->dividend->id,
                            'member_id' => $member->id,
                            'shares_balance' => $memberShares,
                            'dividend_amount' => $dividendAmount,
                            'status' => 'pending'
                        ]);
                    }
                }
            }

            // Update dividend status
            $this->dividend->update([
                'status' => 'calculated',
                'calculation_date' => now()
            ]);
        });
    }
}