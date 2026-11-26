<?php

use App\Models\Account;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\LoanRepayment;
use App\Services\Loans\LoanRepaymentService;
use Illuminate\Support\Str;
use Tests\Support\Scenario;

beforeEach(function () {
    Scenario::chart();
    $this->admin = Scenario::user('admin');
    $this->service = app(LoanRepaymentService::class);
    $this->member = Scenario::member();
    $this->deposits = Scenario::account($this->member, 'share_deposits', 5000);
    $this->loan = Scenario::loan($this->member);
});

// Returns a ledger account's running balance by its code.
function chartBalance(string $code): float
{
    return (float) ChartOfAccount::where('account_code', $code)->value('current_balance');
}

// Returns the member's loan account balance.
function loanAccountBalance($member): float
{
    return (float) Account::where('member_id', $member->id)->where('account_type', 'loan_outstanding')->value('balance');
}

it('records a cash repayment and posts it to cash, loans and interest income', function () {
    $result = $this->service->record($this->loan, 1100, 'cash', (string) Str::uuid(), $this->admin);

    expect($result['status'])->toBe('posted')
        ->and(chartBalance('11100'))->toBe(1100.0)
        ->and(chartBalance('11301'))->toBe(-1000.0)
        ->and(chartBalance('41100'))->toBe(100.0)
        ->and((float) $this->deposits->fresh()->balance)->toBe(5000.0)
        ->and(loanAccountBalance($this->member))->toBe(1000.0)
        ->and((float) $this->loan->fresh()->outstanding_balance)->toBe(1100.0);
})->group('loans');

it('takes a repayment from deposits when the member pays from savings', function () {
    $this->service->record($this->loan, 1100, 'deposits', (string) Str::uuid(), $this->admin);

    expect((float) $this->deposits->fresh()->balance)->toBe(3900.0)->and(chartBalance('21100'))->toBe(-1100.0);
})->group('loans');

it('spreads one payment over several instalments', function () {
    $result = $this->service->record($this->loan, 1650, 'cash', (string) Str::uuid(), $this->admin);
    $instalments = LoanRepayment::where('loan_id', $this->loan->id)->orderBy('due_date')->get();

    expect($instalments[0]->status)->toBe('paid')
        ->and($instalments[1]->status)->toBe('partial')
        ->and((float) $instalments[1]->outstanding_amount)->toBe(550.0)
        ->and($result['split'])->toBe(['penalty' => 0.0, 'interest' => 200.0, 'principal' => 1450.0]);
})->group('loans');

it('completes the loan when it is repaid early in full', function () {
    $result = $this->service->record($this->loan, 2200, 'cash', (string) Str::uuid(), $this->admin);

    expect($result['completed'])->toBeTrue()
        ->and($this->loan->fresh()->status)->toBe('completed')
        ->and(loanAccountBalance($this->member))->toBe(0.0);
})->group('loans');

it('refuses a repayment larger than what is owed', function () {
    expect(fn () => $this->service->record($this->loan, 2300, 'cash', (string) Str::uuid(), $this->admin))
        ->toThrow(DomainException::class, 'exceeds');

    expect(JournalEntry::count())->toBe(0);
})->group('loans');

it('refuses a repayment from deposits that exceeds the available balance', function () {
    $this->deposits->update(['available_balance' => 200]);

    expect(fn () => $this->service->record($this->loan, 1100, 'deposits', (string) Str::uuid(), $this->admin))
        ->toThrow(DomainException::class, 'Insufficient');
})->group('loans');

it('records the same request key only once', function () {
    $key = (string) Str::uuid();

    $this->service->record($this->loan, 1100, 'cash', $key, $this->admin);
    $again = $this->service->record($this->loan, 1100, 'cash', $key, $this->admin);

    expect($again['status'])->toBe('duplicate')->and(JournalEntry::count())->toBe(1);
})->group('loans');

it('recognises a repeated interest only payment', function () {
    $key = (string) Str::uuid();

    $this->service->record($this->loan, 100, 'cash', $key, $this->admin);
    $again = $this->service->record($this->loan, 100, 'cash', $key, $this->admin);

    expect($again['status'])->toBe('duplicate')->and(JournalEntry::count())->toBe(1);
})->group('loans');

it('keeps repayment recording to staff', function () {
    $payload = ['amount' => 1100, 'source' => 'cash', 'request_key' => (string) Str::uuid()];

    $this->actingAs($this->member->user)
        ->post(route('loans.record-repayment', $this->loan), $payload);

    expect(JournalEntry::count())->toBe(0);
})->group('loans');

it('lets staff record a repayment through the form', function () {
    $this->actingAs($this->admin)
        ->post(route('loans.record-repayment', $this->loan), ['amount' => 1100, 'source' => 'cash', 'request_key' => (string) Str::uuid()])
        ->assertSessionHasNoErrors();

    expect((float) $this->loan->fresh()->outstanding_balance)->toBe(1100.0);
})->group('loans');

it('rejects a repayment form with bad input', function (array $payload, string $field) {
    $this->actingAs($this->admin)
        ->post(route('loans.record-repayment', $this->loan), $payload)
        ->assertSessionHasErrors($field);
})->with([
    'missing amount' => [['source' => 'cash', 'request_key' => '6f1c1e0e-5d1c-4f0a-9a38-2b8f1d3c9a10'], 'amount'],
    'negative amount' => [['amount' => -1, 'source' => 'cash', 'request_key' => '6f1c1e0e-5d1c-4f0a-9a38-2b8f1d3c9a10'], 'amount'],
    'unknown source' => [['amount' => 100, 'source' => 'barter', 'request_key' => '6f1c1e0e-5d1c-4f0a-9a38-2b8f1d3c9a10'], 'source'],
    'bad key' => [['amount' => 100, 'source' => 'cash', 'request_key' => 'not-a-uuid'], 'request_key'],
])->group('loans');