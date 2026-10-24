<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LedgerService
{
    public function accountId(string $role): int
    {
        $code = config("sepu_ledger.accounts.{$role}");

        if (!$code) {
            throw new DomainException("Ledger role '{$role}' is not configured.");
        }

        $id = ChartOfAccount::where('account_code', $code)->value('id');

        if (!$id) {
            throw new DomainException("Ledger account {$code} ('{$role}') does not exist in the chart of accounts.");
        }

        return (int) $id;
    }


    public function post(array $lines,string $description,?Model $reference = null,?string $idempotencyKey = null,?int $userId = null,?string $date = null,): JournalEntry {
       
    $normalized = [];
        $debitCents = 0;
        $creditCents = 0;

        foreach ($lines as $line) {
            $cents = (int) round(((float) $line['amount']) * 100);

            if ($cents <= 0) {
                throw new DomainException('Journal line amounts must be greater than zero.');
            }
            if (!in_array($line['type'], ['debit', 'credit'], true)) {
                throw new DomainException("Invalid journal line type '{$line['type']}'.");
            }

            $accountId = ctype_digit((string) $line['account'])
                ? (int) $line['account']
                : $this->accountId((string) $line['account']);

            $normalized[] = [
                'account_id'  => $accountId,
                'type'        => $line['type'],
                'cents'       => $cents,
                'description' => $line['description'] ?? null,
            ];

            $line['type'] === 'debit' ? $debitCents += $cents : $creditCents += $cents;
        }

        if (count($normalized) < 2 || $debitCents !== $creditCents) {
            throw new DomainException('Journal entry is not balanced.');
        }

        return DB::transaction(function () use ($normalized, $debitCents, $creditCents, $description, $reference, $idempotencyKey, $userId, $date) {
            // Lock every account in a stable order (prevents deadlocks, serialises concurrent postings)
            $ids = collect($normalized)->pluck('account_id')->unique()->sort()->values();
            $accounts = ChartOfAccount::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            // Idempotency check AFTER locking, so concurrent retries cannot both pass it
            if ($idempotencyKey) {
                $existing = JournalEntry::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing;
                }
            }

            foreach ($ids as $id) {
                $account = $accounts->get($id);

                if (!$account || !$account->is_active) {
                    throw new DomainException("Ledger account #{$id} does not exist or is inactive.");
                }
                if ($account->account_type === 'header') {
                    throw new DomainException("Cannot post to header account {$account->account_code} {$account->account_name}.");
                }
            }

            $actor = $userId ?? auth()->id();

            $entry = JournalEntry::create([
                'entry_number'    => 'TMP-' . Str::uuid(),
                'entry_date'      => $date ?? now()->toDateString(),
                'reference_type'  => $reference ? get_class($reference) : null,
                'reference_id'    => $reference?->getKey(),
                'description'     => $description,
                'total_debit'     => $debitCents / 100,
                'total_credit'    => $creditCents / 100,
                'status'          => 'posted',
                'created_by'      => $actor,
                'posted_by'       => $actor,
                'posted_at'       => now(),
                'idempotency_key' => $idempotencyKey,
            ]);

            $entry->update(['entry_number' => 'JE-' . str_pad((string) $entry->id, 8, '0', STR_PAD_LEFT)]);

            foreach ($normalized as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'entry_type'       => $line['type'],
                    'amount'           => $line['cents'] / 100,
                    'description'      => $line['description'],
                ]);

                $account = $accounts->get($line['account_id']);
                $sign = $line['type'] === $this->normalSide($account) ? 1 : -1;

                $account->increment('current_balance', $sign * ($line['cents'] / 100));
            }

            return $entry;
        });
    }

    /**
     * Reverse a posted entry by posting its mirror image. The original is marked 'reversed'.
     */
    public function reverse(JournalEntry $entry, string $reason, ?int $userId = null, ?string $idempotencyKey = null): JournalEntry
    {
        return DB::transaction(function () use ($entry, $reason, $userId, $idempotencyKey) {
            $original = JournalEntry::whereKey($entry->id)->lockForUpdate()->firstOrFail();

            if ($original->status === 'reversed') {
                throw new DomainException('This journal entry has already been reversed.');
            }
            if ($original->status !== 'posted') {
                throw new DomainException('Only posted journal entries can be reversed.');
            }

            $mirror = $original->lines()->get()->map(fn ($l) => [
                'account'     => (int) $l->account_id,
                'type'        => $l->entry_type === 'debit' ? 'credit' : 'debit',
                'amount'      => $l->amount,
                'description' => 'Reversal: ' . ($l->description ?? $original->entry_number),
            ])->all();

            $reversal = $this->post(
                $mirror,
                "Reversal of {$original->entry_number}: {$reason}",
                null,
                $idempotencyKey,
                $userId,
            );

            $reversal->update([
                'reversal_of_id' => $original->id,
                'reference_type' => $original->reference_type,
                'reference_id'   => $original->reference_id,
            ]);

            $original->update(['status' => 'reversed']);

            return $reversal;
        });
    }

    private function normalSide(ChartOfAccount $account): string
    {
        return in_array($account->account_type, ['asset', 'expense'], true) ? 'debit' : 'credit';
    }
}