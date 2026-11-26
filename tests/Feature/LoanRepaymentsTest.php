<?php

use App\Models\Account;
use App\Models\LoanRepayment;
use App\Models\Transaction;
use App\Services\Schedule\ScheduleRunService;
use Tests\Support\Scenario;

beforeEach(function () {
    Scenario::chart();
    $this->admin = Scenario::user('admin');
    $this->runner = app(ScheduleRunService::class);
    $this->member = Scenario::member();
    $this->deposits = Scenario::account($this->member, 'share_deposits', 5000);
    $this->loan = Scenario::loan($this->member);
    $this->first = LoanRepayment::where('loan_id', $this->loan->id)->orderBy('due_date')->first();
    $this->second = LoanRepayment::where('loan_id', $this->loan->id)->orderByDesc('due_date')->first();
    Scenario::financeConfig($this->member, ['monthly_contribution' => 0, 'contribution_active' => false]);
});

// Returns the member's loan account holding the outstanding principal.
function loanAccount($member): Account
{
    return Account::where('member_id', $member->id)->where('account_type', 'loan_outstanding')->first();
}

it('deducts an instalment from deposits and reduces the loan', function () {
    $result = $this->runner->postLoanRepayment($this->first->id, 1100, '2026-11', $this->admin);

    expect($result['status'])->toBe('posted')
        ->and((float) $this->deposits->fresh()->balance)->toBe(3900.0)
        ->and((float) loanAccount($this->member)->balance)->toBe(1000.0)
        ->and($this->first->fresh()->status)->toBe('paid')
        ->and((float) $this->loan->fresh()->outstanding_balance)->toBe(1100.0)
        ->and($this->loan->fresh()->status)->toBe('active');
})->group('schedule');

it('never deducts the same instalment twice in a month', function () {
    $this->runner->postLoanRepayment($this->first->id, 1100, '2026-11', $this->admin);
    $again = $this->runner->postLoanRepayment($this->first->id, 1100, '2026-11', $this->admin);

    expect($again['status'])->toBe('skipped')
        ->and((float) $this->deposits->fresh()->balance)->toBe(3900.0)
        ->and(Transaction::where('idempotency_key', ScheduleRunService::repaymentKey($this->first->id, '2026-11'))->count())->toBe(1);
})->group('schedule');

it('takes only what the deposits hold and pays interest before principal', function () {
    $this->deposits->update(['balance' => 500, 'available_balance' => 500]);

    $result = $this->runner->postLoanRepayment($this->first->id, 1100, '2026-11', $this->admin);
    $loan = $this->loan->fresh();

    expect($result['amount'])->toBe(500.0)
        ->and($this->first->fresh()->status)->toBe('partial')
        ->and((float) $this->first->fresh()->outstanding_amount)->toBe(600.0)
        ->and((float) $loan->interest_balance)->toBe(100.0)
        ->and((float) $loan->principal_balance)->toBe(1600.0);
})->group('schedule');

it('cannot touch deposits locked as guarantees', function () {
    $this->deposits->update(['available_balance' => 300]);

    $result = $this->runner->postLoanRepayment($this->first->id, 1100, '2026-11', $this->admin);

    expect($result['amount'])->toBe(300.0)->and((float) $this->deposits->fresh()->balance)->toBe(4700.0);
})->group('schedule');

it('fails clearly when nothing is available to deduct', function () {
    $this->deposits->update(['balance' => 0, 'available_balance' => 0]);

    expect(fn () => $this->runner->postLoanRepayment($this->first->id, 1100, '2026-11', $this->admin))
        ->toThrow(DomainException::class, 'Insufficient available deposits');
})->group('schedule');

it('completes the loan once the last instalment is paid', function () {
    $this->runner->postLoanRepayment($this->first->id, 1100, '2026-11', $this->admin);
    $result = $this->runner->postLoanRepayment($this->second->id, 1100, '2026-12', $this->admin);

    $loan = $this->loan->fresh();

    expect($result['completed'])->toBeTrue()
        ->and($loan->status)->toBe('completed')
        ->and((float) $loan->outstanding_balance)->toBe(0.0)
        ->and((float) loanAccount($this->member)->balance)->toBe(0.0);
})->group('schedule');

it('lists only instalments still to run and marks the month done when none are left', function () {
    $this->actingAs($this->admin)
        ->get(route('schedule.loan-repayment', ['month' => '2026-11']))
        ->assertInertia(fn ($page) => $page->has('rows', 1)->where('alreadyRun', false));

    $this->actingAs($this->admin)
        ->post(route('schedule.loan-repayment.run'), [
            'month' => '2026-11',
            'entries' => [['repayment_id' => $this->first->id, 'deduct_amount' => 1100]],
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs($this->admin)
        ->get(route('schedule.loan-repayment', ['month' => '2026-11']))
        ->assertInertia(fn ($page) => $page->has('rows', 0)->where('alreadyRun', true));
})->group('schedule');

it('rejects a deduction amount that is not a positive money value', function (mixed $amount) {
    $this->actingAs($this->admin)
        ->post(route('schedule.loan-repayment.run'), [
            'month' => '2026-11',
            'entries' => [['repayment_id' => $this->first->id, 'deduct_amount' => $amount]],
        ])
        ->assertSessionHasErrors('entries.0.deduct_amount');
})->with([0, -5, 'abc', 10.555])->group('schedule');