<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccountBalanceService
{
    private const DIRECTION = [
        'deposit'                    => 'credit',
        'share_capital_contribution' => 'credit',
        'loan_disbursement'          => 'credit',
        'withdrawal'                 => 'debit',
        'loan_repayment'             => 'debit',
        'fee'                        => 'debit',
        'penalty'                    => 'debit',
    ];

    private const LEDGER_ROLE = [
        'share_deposits'   => 'member_deposits',
        'share_capital'    => 'share_capital',
        'dividend'         => 'dividends_payable',
        'loan_outstanding' => 'loans_performing',
    ];

    public function __construct(private LedgerService $ledger) {}


    public function credit(int $accountId, float|int|string $amount, array $attrs = []): Transaction
    {
        return $this->apply('credit', $accountId, $amount, $attrs);
    }

    public function debit(int $accountId, float|int|string $amount, array $attrs = []): Transaction
    {
        return $this->apply('debit', $accountId, $amount, $attrs);
    }


    public function settlePending(Transaction $pending, User $approver): Transaction
    {
        return DB::transaction(function () use ($pending, $approver) {
            $tx = Transaction::whereKey($pending->id)->lockForUpdate()->firstOrFail();

            if ($tx->status !== 'pending') {
                throw new DomainException('Only pending transactions can be approved.');
            }

            $this->assertMakerChecker($tx, $approver);

            $direction = self::DIRECTION[$tx->transaction_type] ?? null;
            if (!$direction) {
                throw new DomainException("Transactions of type '{$tx->transaction_type}' cannot be approved here.");
            }

            $account = Account::whereKey($tx->account_id)->lockForUpdate()->firstOrFail();
            [$before, $after] = $this->move($direction, $account, $this->toCents($tx->amount));

            $tx->update([
                'status'         => 'completed',
                'balance_before' => $before / 100,
                'balance_after'  => $after / 100,
                'approved_by'    => $approver->id,
                'approved_at'    => now(),
                'processed_by'   => $approver->id,
                'processed_at'   => now(),
            ]);

            $entryId = $this->postLedger($tx, $account, $direction, $approver->id);
            if ($entryId) {
                $tx->update(['journal_entry_id' => $entryId]);
            }

            return $tx->refresh();
        });
    }


    public function reverse(Transaction $original, string $reason, User $user): Transaction
    {
        return DB::transaction(function () use ($original, $reason, $user) {
            $orig = Transaction::whereKey($original->id)->lockForUpdate()->firstOrFail();

            if ($orig->status !== 'completed') {
                throw new DomainException('Only completed transactions can be reversed.');
            }
            if ($orig->reversal_of_id) {
                throw new DomainException('A reversal cannot itself be reversed.');
            }

            $originalDirection = self::DIRECTION[$orig->transaction_type] ?? null;
            if (!$originalDirection) {
                throw new DomainException("Transactions of type '{$orig->transaction_type}' cannot be reversed here yet.");
            }

            $direction = $originalDirection === 'credit' ? 'debit' : 'credit';

            $reversal = $this->apply($direction, $orig->account_id, $orig->amount, [
                'transaction_type' => 'reversal',
                'description'      => 'Reversal of ' . $orig->transaction_id,
                'payment_method'   => $orig->payment_method,
                'reference_number' => $orig->transaction_id,
                'processed_by'     => $user->id,
                'created_by'       => $user->id,
                'approved_by'      => $user->id,
                'reversal_of_id'   => $orig->id,
                'idempotency_key'  => "tx:{$orig->id}:reverse",
                'metadata'         => [
                    'original_transaction_id' => $orig->id,
                    'reversal_reason'         => $reason,
                    'reversed_by'             => $user->id,
                    'reversed_at'             => now()->toDateTimeString(),
                ],
            ]);

            if ($orig->journal_entry_id) {
                $entry = $this->ledger->reverse(
                    JournalEntry::findOrFail($orig->journal_entry_id),
                    $reason,
                    $user->id,
                    "tx:{$orig->id}:ledger-reverse",
                );
                $reversal->update(['journal_entry_id' => $entry->id]);
            }

            $orig->update([
                'status'   => 'reversed',
                'metadata' => array_merge($orig->metadata ?? [], ['reversal_transaction_id' => $reversal->id]),
            ]);

            return $reversal;
        });
    }


    private function apply(string $direction, int $accountId, float|int|string $amount, array $attrs): Transaction
    {
        $cents = $this->toCents($amount);

        if($cents <= 0){
            throw new DomainException('Amount must be greater than zero');
        }

        return DB::transaction(function () use ($direction, $accountId, $cents, $attrs) {
            $account = Account::whereKey($accountId)->lockForUpdate()->firstOrFail();

            if (!empty($attrs['idempotency_key'])) {
                $existing = Transaction::where('idempotency_key', $attrs['idempotency_key'])->first();
                if ($existing) {
                    return $existing;
                }
            }

            [$before, $after] = $this->move($direction, $account, $cents);

            return Transaction::create([
                'transaction_id'    => $this->newTransactionId(),
                'account_id'        => $account->id,
                'member_id'         => $account->member_id,
                'transaction_type'  => $attrs['transaction_type'] ?? ($direction === 'credit' ? 'deposit' : 'withdrawal'),
                'amount'            => $cents / 100,
                'balance_before'    => $before / 100,
                'balance_after'     => $after / 100,
                'description'       => $attrs['description'] ?? ucfirst($direction),
                'reference_number'  => $attrs['reference_number'] ?? null,
                'payment_method'    => $attrs['payment_method'] ?? null,
                'payment_reference' => $attrs['payment_reference'] ?? null,
                'status'            => 'completed',
                'processed_by'      => $attrs['processed_by'] ?? auth()->id(),
                'processed_at'      => now(),
                'created_by'        => $attrs['created_by'] ?? auth()->id(),
                'approved_by'       => $attrs['approved_by'] ?? null,
                'approved_at'       => isset($attrs['approved_by']) ? now() : null,
                'idempotency_key'   => $attrs['idempotency_key'] ?? null,
                'reversal_of_id'    => $attrs['reversal_of_id'] ?? null,
                'metadata'          => $attrs['metadata'] ?? null,
            ]);
        });
    }


    private function move(string $direction, Account $account, int $cents): array
    {
        if (!$account->is_active) {
            throw new DomainException('This account is not active.');
        }

        $isLoanAccount = $account->account_type === 'loan_outstanding';
        $before    = $this->toCents($account->balance);
        $available = $this->toCents($account->available_balance);
        $delta     = $direction === 'credit' ? $cents : -$cents;

        if ($direction === 'debit') {
            $spendable = $isLoanAccount ? $before : $available;

            if ($cents > $spendable) {
                throw new DomainException(
                    $isLoanAccount
                        ? 'Amount exceeds the outstanding loan balance.'
                        : 'Insufficient available balance.'
                );
            }
        }

        $after = $before + $delta;

        $account->balance = $after / 100;
        if (!$isLoanAccount) {
            $account->available_balance = ($available + $delta) / 100;
        }
        $account->last_transaction_at = now();
        $account->save();

        return [$before, $after];
    }

    private function assertMakerChecker(Transaction $tx, User $approver): void
    {
        if (!config('sepu.maker_checker')) {
            return;
        }

        if ($tx->created_by && (int) $tx->created_by === (int) $approver->id) {
            throw new DomainException('You cannot approve a transaction you created. Another authorised user must approve it.');
        }
    }

    private function postLedger(Transaction $tx, Account $account, string $direction, ?int $userId): ?int
    {
        $role = self::LEDGER_ROLE[$account->account_type] ?? null;

        if (!$role || !in_array($tx->transaction_type, ['deposit', 'share_capital_contribution', 'withdrawal'], true)) {
            Log::warning('No ledger rule for transaction; balance changed without a journal entry', [
                'transaction_id' => $tx->transaction_id,
                'type'           => $tx->transaction_type,
            ]);

            return null;
        }

        $method = $tx->payment_method ?: 'cash';
        $cashByMethod = config('sepu_ledger.cash_by_method');

        if (empty($cashByMethod[$method])) {
            throw new DomainException("Payment method '{$method}' has no ledger cash account configured.");
        }

        $cash = $cashByMethod[$method];
        $desc = "{$tx->description} ({$tx->transaction_id})";

        $lines = $direction === 'credit'
            ? [
                ['account' => $cash, 'type' => 'debit',  'amount' => $tx->amount, 'description' => $desc],
                ['account' => $role, 'type' => 'credit', 'amount' => $tx->amount, 'description' => $desc],
            ]
            : [
                ['account' => $role, 'type' => 'debit',  'amount' => $tx->amount, 'description' => $desc],
                ['account' => $cash, 'type' => 'credit', 'amount' => $tx->amount, 'description' => $desc],
            ];

        return $this->ledger->post($lines, $desc, $tx, "tx:{$tx->id}:post", $userId)->id;
    }

    private function toCents(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function newTransactionId(): string
    {
        do {
            $id = 'TXN' . date('Ymd') . strtoupper(Str::random(6));
        } while (Transaction::where('transaction_id', $id)->exists());

        return $id;
    }
}
