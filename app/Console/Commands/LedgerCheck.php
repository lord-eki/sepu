<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use Illuminate\Console\Command;

class LedgerCheck extends Command
{
    protected $signature = 'sepu:ledger-check';
    protected $description = 'Verify config/sepu_ledger.php maps every role to an existing, active, postable account';

    public function handle(): int
    {
        $failed = false;
        $rows = [];

        foreach (config('sepu_ledger.accounts') as $role => $code) {
            $acct = ChartOfAccount::where('account_code', $code)->first();

            $status = match (true) {
                !$acct                             => 'MISSING',
                !$acct->is_active                  => 'INACTIVE',
                $acct->account_type === 'header'   => 'HEADER (not postable)',
                default                            => 'ok',
            };

            $failed = $failed || $status !== 'ok';
            $rows[] = [$role, $code, $acct->account_name ?? '-', $status];
        }

        $this->table(['Role', 'Code', 'Account', 'Status'], $rows);

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}