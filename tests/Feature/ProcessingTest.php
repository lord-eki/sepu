<?php

use App\Enums\LoanMigrationBatchStatus;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\ChartOfAccount;
use App\Models\Loan;
use App\Models\LoanMigrationBatch;
use App\Services\Loans\LoanRepaymentService;
use App\Services\LoanMigration\LoanMigrationProcessor;
use App\Services\LoanMigration\LoanMigrationWorkflow;
use Illuminate\Support\Str;
use Tests\Support\Scenario;

beforeEach(function () {
    config(['sepu.maker_checker' => false]);
    Scenario::chart();
    $this->officer = Scenario::user('loan_officer');
    $this->accountant = Scenario::user('accountant');
    $this->admin = Scenario::user('admin');
    $this->processor = app(LoanMigrationProcessor::class);
    $this->batch = approvedBatch($this->officer, $this->accountant, $this->admin);
});

// Builds a batch and walks it through validation, submission, verification and approval.
function approvedBatch($officer, $accountant, $admin, int $records = 2): LoanMigrationBatch
{
    $batch = Scenario::migrationBatch($officer, $records);
    $workflow = app(LoanMigrationWorkflow::class);

    $workflow->validate($batch, $officer);
    $workflow->submit($batch, $officer);
    $workflow->verifyAccounts($batch, $accountant);

    return $workflow->approve($batch, $admin);
}

// Returns a ledger account's running balance by its code.
function ledgerBalance(string $code): float
{
    return (float) ChartOfAccount::where('account_code', $code)->value('current_balance');
}

it('creates an active loan with its remaining schedule for each record', function () {
    $result = $this->processor->process($this->batch, $this->admin);
    $loan = Loan::where('loan_number', $this->batch->records()->first()->loan_number)->first();

    expect($result['created'])->toBe(2)
        ->and($result['failed'])->toBe([])
        ->and(Loan::count())->toBe(2)
        ->and($loan->status)->toBe('active')
        ->and((float) $loan->principal_balance)->toBe(6000.0)
        ->and($loan->repayments()->count())->toBe(6)
        ->and(round((float) $loan->repayments()->sum('principal_amount'), 2))->toBe(6000.0)
        ->and($loan->repayments()->orderBy('due_date')->first()->due_date->toDateString())->toBe('2026-12-01');
})->group('migration');

it('puts the outstanding principal on the loan account and in the ledger', function () {
    $this->processor->process($this->batch, $this->admin);

    $loanAccounts = Account::where('account_type', 'loan_outstanding')->pluck('balance')->map(fn ($balance) => (float) $balance)->all();

    expect($loanAccounts)->toBe([6000.0, 6000.0])
        ->and(ledgerBalance('11301'))->toBe(12000.0)
        ->and(ledgerBalance('33000'))->toBe(12000.0);
})->group('migration');

it('marks the records and the batch as processed and writes the audit trail', function () {
    $this->processor->process($this->batch, $this->admin);

    $batch = $this->batch->fresh();

    expect($batch->status)->toBe(LoanMigrationBatchStatus::PROCESSED)
        ->and($batch->processed_records)->toBe(2)
        ->and($batch->records()->whereNotNull('loan_id')->count())->toBe(2)
        ->and(AuditLog::where('action', 'loan_migration.loan_created')->count())->toBe(2);
})->group('migration');

it('cannot be processed twice', function () {
    $this->processor->process($this->batch, $this->admin);

    expect(fn () => $this->processor->process($this->batch, $this->admin))->toThrow(DomainException::class);
    expect(Loan::count())->toBe(2);
})->group('migration');

it('refuses a batch that is not approved', function () {
    $draft = Scenario::migrationBatch($this->officer);

    expect(fn () => $this->processor->process($draft, $this->admin))->toThrow(DomainException::class, 'approved');
})->group('migration');

it('keeps going when one record fails and finishes the rest on a second run', function () {
    $blocker = Scenario::loan(Scenario::member());
    $second = $this->batch->records()->orderByDesc('id')->first();
    $original = $second->loan_number;
    $second->update(['loan_number' => $blocker->loan_number]);

    $first = $this->processor->process($this->batch, $this->admin);

    expect($first['created'])->toBe(1)
        ->and($first['failed'])->toBe([$blocker->loan_number])
        ->and($this->batch->fresh()->status)->toBe(LoanMigrationBatchStatus::APPROVED);

    $second->update(['loan_number' => $original]);

    $again = $this->processor->process($this->batch, $this->admin);

    expect($again['created'])->toBe(1)->and($this->batch->fresh()->status)->toBe(LoanMigrationBatchStatus::PROCESSED);
})->group('migration');

it('migrates a cleared loan as completed with no schedule and no balance', function () {
    $batch = Scenario::migrationBatch($this->officer, 1);
    $batch->records()->first()->update(['loan_status' => 'cleared', 'outstanding_balance' => 0, 'remaining_period' => 0, 'next_due_date' => null, 'total_amount_paid' => 10000]);
    $batch->update(['total_amount_paid' => 10000, 'total_outstanding_balance' => 0]);

    $workflow = app(LoanMigrationWorkflow::class);
    $workflow->validate($batch, $this->officer);
    $workflow->submit($batch, $this->officer);
    $workflow->verifyAccounts($batch, $this->accountant);
    $workflow->approve($batch, $this->admin);

    $this->processor->process($batch, $this->admin);
    $loan = Loan::where('loan_number', $batch->records()->first()->loan_number)->first();

    expect($loan->status)->toBe('completed')
        ->and($loan->repayments()->count())->toBe(0)
        ->and((float) $loan->outstanding_balance)->toBe(0.0);
})->group('migration');

it('marks instalments already past due as overdue', function () {
    $batch = Scenario::migrationBatch($this->officer, 1);
    $batch->records()->first()->update(['next_due_date' => '2026-01-01']);

    $workflow = app(LoanMigrationWorkflow::class);
    $workflow->validate($batch, $this->officer);
    $workflow->submit($batch, $this->officer);
    $workflow->verifyAccounts($batch, $this->accountant);
    $workflow->approve($batch, $this->admin);

    $this->processor->process($batch, $this->admin);
    $loan = Loan::where('loan_number', $batch->records()->first()->loan_number)->first();

    expect($loan->repayments()->orderBy('due_date')->first()->status)->toBe('overdue')
        ->and($loan->days_in_arrears)->toBeGreaterThan(0);
})->group('migration');

it('lets a migrated loan be repaid like any other loan', function () {
    $this->processor->process($this->batch, $this->admin);
    $loan = Loan::orderBy('id')->first();
    $first = $loan->repayments()->orderBy('due_date')->first();
    $before = (float) $loan->outstanding_balance;

    app(LoanRepaymentService::class)->record($loan, (float) $first->expected_amount, 'cash', (string) Str::uuid(), $this->admin);

    expect(round((float) $loan->fresh()->outstanding_balance, 2))->toBe(round($before - (float) $first->expected_amount, 2))
        ->and($first->fresh()->status)->toBe('paid');
})->group('migration');

it('lets a loan officer process through the form and blocks an accountant', function () {
    $this->actingAs($this->accountant)
        ->post(route('loan-migration.process', $this->batch))
        ->assertForbidden();

    expect(Loan::count())->toBe(0);

    $this->actingAs($this->officer)
        ->post(route('loan-migration.process', $this->batch))
        ->assertSessionHasNoErrors();

    expect(Loan::count())->toBe(2);
})->group('migration');