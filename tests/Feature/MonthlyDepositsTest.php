<?php

use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Services\Schedule\ScheduleRunService;
use Tests\Support\Scenario;

beforeEach(function () {
    Scenario::chart();
    $this->admin = Scenario::user('admin');
    $this->runner = app(ScheduleRunService::class);
});

// Creates a member with a deposits account and a monthly contribution setup.
function contributor(float $monthly = 2000): array
{
    $member = Scenario::member();
    $account = Scenario::account($member, 'share_deposits', 0);
    Scenario::financeConfig($member, ['contribution_account_id' => $account->id, 'monthly_contribution' => $monthly]);

    return [$member, $account];
}

it('posts a member deposit once per month', function () {
    [$member, $account] = contributor();

    $first = $this->runner->postMonthlyDeposit($member->id, '2026-11', $this->admin);
    $second = $this->runner->postMonthlyDeposit($member->id, '2026-11', $this->admin);

    expect($first['status'])->toBe('posted')
        ->and($second['status'])->toBe('skipped')
        ->and(Transaction::where('account_id', $account->id)->count())->toBe(1)
        ->and((float) $account->fresh()->balance)->toBe(2000.0);
})->group('schedule');

it('lets a member left out of a run be posted later in the same month', function () {
    [$first, $firstAccount] = contributor();
    [$second, $secondAccount] = contributor();

    $this->runner->postMonthlyDeposit($first->id, '2026-11', $this->admin);

    $this->actingAs($this->admin)
        ->get(route('schedule.monthly-deposit', ['month' => '2026-11']))
        ->assertInertia(fn ($page) => $page->where('summary.pending', 1)->where('summary.already_done', 1)->where('alreadyRun', false));

    $this->actingAs($this->admin)
        ->post(route('schedule.monthly-deposit.run'), ['month' => '2026-11', 'entries' => [['member_id' => $second->id]]])
        ->assertSessionHasNoErrors();

    expect((float) $secondAccount->fresh()->balance)->toBe(2000.0);

    $this->actingAs($this->admin)
        ->get(route('schedule.monthly-deposit', ['month' => '2026-11']))
        ->assertInertia(fn ($page) => $page->where('summary.pending', 0)->where('alreadyRun', true));
})->group('schedule');

it('uses the configured amount and account whatever the browser sends', function () {
    [$member, $account] = contributor(2000);

    $this->actingAs($this->admin)
        ->post(route('schedule.monthly-deposit.run'), [
            'month' => '2026-11',
            'entries' => [['member_id' => $member->id, 'amount' => 1]],
        ]);

    expect((float) $account->fresh()->balance)->toBe(2000.0);
})->group('schedule');

it('records a journal entry for each scheduled deposit', function () {
    [$member] = contributor();

    $this->runner->postMonthlyDeposit($member->id, '2026-11', $this->admin);

    $transaction = Transaction::where('member_id', $member->id)->first();

    expect(JournalEntry::count())->toBe(1)->and($transaction->journal_entry_id)->not->toBeNull();
})->group('schedule');

it('skips a member who already has a deposit from the old schedule code', function () {
    [$member, $account] = contributor();

    (new Transaction())->forceFill([
        'transaction_id' => 'DEP-SCH-LEGACY1',
        'account_id' => $account->id,
        'member_id' => $member->id,
        'transaction_type' => 'deposit',
        'amount' => 2000,
        'balance_before' => 0,
        'balance_after' => 2000,
        'description' => 'Schedule: Monthly deposit',
        'status' => 'completed',
        'processed_at' => '2026-11-05 10:00:00',
    ])->save();

    $result = $this->runner->postMonthlyDeposit($member->id, '2026-11', $this->admin);

    expect($result['status'])->toBe('skipped');
})->group('schedule');

it('rejects a month that is not in Y-m format', function () {
    [$member] = contributor();

    $this->actingAs($this->admin)
        ->post(route('schedule.monthly-deposit.run'), ['month' => 'November', 'entries' => [['member_id' => $member->id]]])
        ->assertSessionHasErrors('month');
})->group('schedule');