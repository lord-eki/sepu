<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\GuaranteeLock;
use App\Models\LoanGuarantor;
use App\Services\Accounting\AccountBalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillGuaranteeLocks extends Command
{
    protected $signature = 'sepu:backfill-guarantee-locks';
    protected $description = 'Lock deposits for accepted guarantees on open loans that have no lock yet';

    public function handle(AccountBalanceService $balances): int
    {
        $locked = 0;
        $failures = [];

        LoanGuarantor::where('status', 'accepted')
            ->whereDoesntHave('lock')
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['pending', 'approved', 'active', 'defaulted']))
            ->with('loan')
            ->chunkById(100, function ($rows) use ($balances, &$locked, &$failures) {
                foreach ($rows as $g) {
                    try {
                        DB::transaction(function () use ($g, $balances) {
                            $account = Account::where('member_id', $g->guarantor_member_id)
                                ->where('account_type', 'share_deposits')
                                ->where('is_active', true)
                                ->orderBy('id')
                                ->first();

                            if (!$account) {
                                throw new \DomainException('no active deposits account');
                            }

                            $balances->hold($account->id, $g->guaranteed_amount);

                            GuaranteeLock::create([
                                'loan_id'             => $g->loan_id,
                                'loan_guarantor_id'   => $g->id,
                                'guarantor_member_id' => $g->guarantor_member_id,
                                'account_id'          => $account->id,
                                'amount'              => $g->guaranteed_amount,
                                'status'              => 'active',
                            ]);
                        });

                        $locked++;
                    } catch (\DomainException $e) {
                        $failures[] = [$g->loan->loan_number ?? $g->loan_id, $g->guarantor_member_id, $e->getMessage()];
                    }
                }
            });

        $this->info("Locked {$locked} guarantee(s).");

        if ($failures) {
            $this->warn('These could not be locked and need attention (guarantor lacks free deposits):');
            $this->table(['Loan', 'Guarantor member id', 'Reason'], $failures);
        }

        return self::SUCCESS;
    }
}