<?php

use App\Models\AuditLog;
use App\Models\Loan;
use App\Services\Loans\LoanRepaymentService;
use App\Services\Loans\LoanService;
use App\Services\Loans\LoanSettlementService;
use App\Services\Loans\TopUpEligibilityService;
use App\Services\LoanMigration\LoanMigrationProcessor;
use App\Services\LoanMigration\LoanMigrationWorkflow;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\Support\Scenario;

beforeEach(function () {
    Carbon::setTestNow('2026-11-27 10:00:00');
    config(['sepu.maker_checker' => false, 'sepu.settlement_interest' => 'accrued']);
    Scenario::chart();
    $this->admin = Scenario::user('admin');
    $this->cashier = Scenario::user('accountant');
    $this->member = Scenario::member();
    Scenario::account($this->member, 'share_deposits', 5000);
    $this->parent = Scenario::loan($this->member, 2, ['allow_top_up' => true, 'top_up_eligibility_percent' => 50, 'max_top_ups' => 1]);
    $this->eligibility = app(TopUpEligibilityService::class);
});

afterEach(fn () => Carbon::setTestNow());

// Pays the first instalment of a loan in cash so half of the principal is repaid.
function repayFirstInstalment(Loan $loan, $user): void
{
    app(LoanRepaymentService::class)->record($loan, 1100, 'cash', (string) Str::uuid(), $user);
}

it('allows a top-up once enough principal is repaid', function () {
    repayFirstInstalment($this->parent, $this->admin);

    $result = $this->eligibility->check($this->parent->fresh());

    expect($result['eligible'])->toBeTrue()
        ->and($result['progress_percent'])->toBe(50.0)
        ->and($result['reasons'])->toBe([]);
})->group('topup');

it('refuses a top-up before the repayment threshold', function () {
    $result = $this->eligibility->check($this->parent);

    expect($result['eligible'])->toBeFalse()
        ->and($result['reasons'][0])->toContain('must be repaid');
})->group('topup');

it('refuses a top-up on a product that does not allow it', function () {
    $loan = Scenario::loan(Scenario::member(), 2, ['allow_top_up' => false]);

    expect($this->eligibility->check($loan)['reasons'])->toContain('This loan product does not allow top-ups.');
})->group('topup');

it('refuses a top-up on a loan that is not active', function () {
    repayFirstInstalment($this->parent, $this->admin);
    $this->parent->update(['status' => 'defaulted']);

    expect($this->eligibility->check($this->parent->fresh())['reasons'])->toContain('Only an active loan can be topped up.');
})->group('topup');

it('refuses a top-up under a different product', function () {
    repayFirstInstalment($this->parent, $this->admin);
    $other = Scenario::loanProduct(['allow_top_up' => true]);

    expect($this->eligibility->check($this->parent->fresh(), $other)['reasons'])
        ->toContain('A top-up must use the same loan product as the loan it tops up.');
})->group('topup');

it('stops once the maximum number of top-ups is reached', function () {
    repayFirstInstalment($this->parent, $this->admin);
    $child = Scenario::topUpLoan($this->parent, ['status' => 'active', 'principal_balance' => 1000, 'approved_amount' => 2000]);

    expect($this->eligibility->check($child)['reasons'])
        ->toContain('The maximum number of top-ups for this loan has been reached.');
})->group('topup');

it('refuses a second top-up while one is already in progress', function () {
    repayFirstInstalment($this->parent, $this->admin);
    Scenario::topUpLoan($this->parent, ['status' => 'pending']);

    expect($this->eligibility->check($this->parent->fresh())['reasons'])
        ->toContain('A top-up application for this loan is already in progress.');
})->group('topup');

it('quotes the settlement with only the interest that has fallen due', function () {
    $quote = app(LoanSettlementService::class)->quote($this->parent);

    expect($quote['principal'])->toBe(2000.0)
        ->and($quote['interest'])->toBe(100.0)
        ->and($quote['waived_interest'])->toBe(100.0)
        ->and($quote['total'])->toBe(2100.0);
})->group('topup');

it('quotes the full remaining interest when the policy is full', function () {
    config(['sepu.settlement_interest' => 'full']);

    $quote = app(LoanSettlementService::class)->quote($this->parent);

    expect($quote['interest'])->toBe(200.0)->and($quote['waived_interest'])->toBe(0.0)->and($quote['total'])->toBe(2200.0);
})->group('topup');

it('settles the parent from the top-up proceeds when the top-up is disbursed', function () {
    $topUp = Scenario::topUpLoan($this->parent);

    $result = app(LoanService::class)->disburse($topUp->id, 'cash', null, $this->cashier);

    $parent = $this->parent->fresh();
    $topUp = $topUp->fresh();

    expect($result['net_disbursement'])->toBe(2850.0)
        ->and($parent->status)->toBe('completed')
        ->and((float) $parent->outstanding_balance)->toBe(0.0)
        ->and($parent->repayments()->where('status', '!=', 'paid')->count())->toBe(0)
        ->and($topUp->status)->toBe('active')
        ->and((float) $topUp->top_up_settlement_amount)->toBe(2100.0)
        ->and((float) $topUp->principal_balance)->toBe(5000.0)
        ->and(Scenario::ledgerBalance('11301'))->toBe(3000.0)
        ->and(Scenario::ledgerBalance('41100'))->toBe(100.0)
        ->and(Scenario::ledgerBalance('21300'))->toBe(0.0)
        ->and(Scenario::ledgerBalance('11100'))->toBe(-2850.0)
        ->and(AuditLog::where('action', 'loan.top_up_settled')->count())->toBe(1);
})->group('topup');

it('keeps the parent running when the product does not settle the existing balance', function () {
    $this->parent->loanProduct->update(['settle_existing_balance' => false]);
    $topUp = Scenario::topUpLoan($this->parent);

    $result = app(LoanService::class)->disburse($topUp->id, 'cash', null, $this->cashier);

    expect($result['net_disbursement'])->toBe(4950.0)->and($this->parent->fresh()->status)->toBe('active');
})->group('topup');

it('refuses a disbursement that would not cover the settlement', function () {
    $topUp = Scenario::topUpLoan($this->parent, ['approved_amount' => 2000, 'applied_amount' => 2000]);

    expect(fn () => app(LoanService::class)->disburse($topUp->id, 'cash', null, $this->cashier))
        ->toThrow(DomainException::class, 'Net disbursement');

    expect($this->parent->fresh()->status)->toBe('active');
})->group('topup');

it('links a migrated top-up to its parent loan', function () {
    $officer = Scenario::user('loan_officer');
    $batch = Scenario::migrationBatch($officer, 1);
    $record = $batch->records()->first();
    $parent = Scenario::loan($record->member);
    $record->update(['is_top_up' => true, 'parent_loan_number' => $parent->loan_number]);

    $workflow = app(LoanMigrationWorkflow::class);
    $workflow->validate($batch, $officer);
    $workflow->submit($batch, $officer);
    $workflow->verifyAccounts($batch, $this->cashier);
    $workflow->approve($batch, $this->admin);

    app(LoanMigrationProcessor::class)->process($batch, $this->admin);

    $child = Loan::where('loan_number', $record->loan_number)->first();

    expect($child->parent_loan_id)->toEqual($parent->id)->and($child->application_type)->toBe('top_up');
})->group('topup');