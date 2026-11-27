<?php

use App\Enums\LoanMigrationBatchStatus;
use App\Models\AuditLog;
use App\Models\LoanMigrationBatch;
use App\Services\LoanMigration\LoanMigrationWorkflow;
use Illuminate\Support\Facades\Gate;
use Tests\Support\Scenario;

beforeEach(function () {
    config(['sepu.maker_checker' => true]);
    $this->officer = Scenario::user('loan_officer');
    $this->accountant = Scenario::user('accountant');
    $this->approver = Scenario::user('admin');
    $this->workflow = app(LoanMigrationWorkflow::class);
    $this->batch = Scenario::migrationBatch($this->officer);
});

it('validates a clean batch', function () {
    $result = $this->workflow->validate($this->batch, $this->officer);

    expect($result->status)->toBe(LoanMigrationBatchStatus::VALIDATED)
        ->and($result->valid_records)->toBe(2)
        ->and($result->invalid_records)->toBe(0);
})->group('migration');

it('flags a record with a problem', function (array $change, string $field) {
    $this->batch->records()->first()->update($change);

    $result = $this->workflow->validate($this->batch, $this->officer);
    $errors = $this->batch->records()->first()->fresh()->validation_errors;

    expect($result->status)->toBe(LoanMigrationBatchStatus::VALIDATION_FAILED)
        ->and($result->invalid_records)->toBe(1)
        ->and($errors)->toHaveKey($field);
})->with([
    'outstanding above original' => [['outstanding_balance' => 20000], 'outstanding_balance'],
    'unknown member number' => [['member_number' => 'NOPE'], 'member_number'],
    'cleared with a balance' => [['loan_status' => 'cleared'], 'loan_status'],
    'restructured status' => [['loan_status' => 'restructured'], 'loan_status'],
    'remaining beyond the term' => [['remaining_period' => 20], 'remaining_period'],
    'future disbursement' => [['date_disbursed' => '2099-01-01'], 'date_disbursed'],
    'top up without a parent' => [['is_top_up' => true, 'parent_loan_number' => null], 'parent_loan_number'],
    'balance without a due date' => [['next_due_date' => null], 'next_due_date'],
])->group('migration');

it('rejects a loan number that already belongs to a loan', function () {
    $existing = Scenario::loan(Scenario::member());
    $this->batch->records()->first()->update(['loan_number' => $existing->loan_number]);

    $this->workflow->validate($this->batch, $this->officer);

    expect($this->batch->records()->first()->fresh()->validation_errors)->toHaveKey('loan_number');
})->group('migration');

it('rejects a loan number used in another batch', function () {
    $other = Scenario::migrationBatch($this->officer, 1);
    $this->batch->records()->first()->update(['loan_number' => $other->records()->first()->loan_number]);

    $this->workflow->validate($this->batch, $this->officer);

    expect($this->batch->records()->first()->fresh()->validation_errors)->toHaveKey('loan_number');
})->group('migration');

it('rejects a top up whose parent belongs to another member', function () {
    $parent = Scenario::loan(Scenario::member());
    $this->batch->records()->first()->update(['is_top_up' => true, 'parent_loan_number' => $parent->loan_number]);

    $this->workflow->validate($this->batch, $this->officer);

    expect($this->batch->records()->first()->fresh()->validation_errors)->toHaveKey('parent_loan_number');
})->group('migration');

it('accepts a top up whose parent belongs to the same member', function () {
    $record = $this->batch->records()->first();
    $parent = Scenario::loan($record->member);
    $record->update(['is_top_up' => true, 'parent_loan_number' => $parent->loan_number]);

    $result = $this->workflow->validate($this->batch, $this->officer);

    expect($result->invalid_records)->toBe(0);
})->group('migration');

it('walks a batch through validate, submit, verify and approve with an audit row for each step', function () {
    $this->workflow->validate($this->batch, $this->officer);
    $this->workflow->submit($this->batch, $this->officer);
    $this->workflow->verifyAccounts($this->batch, $this->accountant);
    $result = $this->workflow->approve($this->batch, $this->approver);

    expect($result->status)->toBe(LoanMigrationBatchStatus::APPROVED)
        ->and($result->submitted_by)->toEqual($this->officer->id)
        ->and($result->verified_by)->toEqual($this->accountant->id)
        ->and($result->approved_by)->toEqual($this->approver->id)
        ->and(AuditLog::where('model_id', $this->batch->id)->count())->toBe(4);
})->group('migration');

it('refuses steps taken out of order', function (string $method) {
    $user = $method === 'verifyAccounts' ? $this->accountant : $this->approver;

    expect(fn () => $this->workflow->{$method}($this->batch, $user))->toThrow(DomainException::class);
})->with(['submit', 'verifyAccounts', 'approve'])->group('migration');

it('refuses to submit a batch that failed validation', function () {
    $this->batch->records()->first()->update(['outstanding_balance' => 20000]);
    $this->workflow->validate($this->batch, $this->officer);

    expect(fn () => $this->workflow->submit($this->batch, $this->officer))->toThrow(DomainException::class);
})->group('migration');

it('refuses to verify when the batch totals no longer match the records', function () {
    $this->workflow->validate($this->batch, $this->officer);
    $this->workflow->submit($this->batch, $this->officer);
    $this->batch->records()->first()->update(['outstanding_balance' => 5000]);

    expect(fn () => $this->workflow->verifyAccounts($this->batch, $this->accountant))
        ->toThrow(DomainException::class, 'totals do not match');
})->group('migration');

it('rejects a submitted batch with a reason', function () {
    $this->workflow->validate($this->batch, $this->officer);
    $this->workflow->submit($this->batch, $this->officer);

    $result = $this->workflow->reject($this->batch, $this->accountant, 'Totals disagree with the ledger.');

    expect($result->status)->toBe(LoanMigrationBatchStatus::REJECTED)->and($result->remarks)->toBe('Totals disagree with the ledger.');
})->group('migration');

it('gives each role only its own step', function () {
    $member = Scenario::member()->user;

    expect(Gate::forUser($this->officer)->allows('capture', $this->batch))->toBeTrue()
        ->and(Gate::forUser($this->accountant)->allows('capture', $this->batch))->toBeFalse()
        ->and(Gate::forUser($this->accountant)->allows('verify', $this->batch))->toBeTrue()
        ->and(Gate::forUser($this->officer)->allows('verify', $this->batch))->toBeFalse()
        ->and(Gate::forUser($member)->allows('access', LoanMigrationBatch::class))->toBeFalse();
})->group('migration');

it('keeps the verifier from also approving while maker-checker is on', function () {
    $this->batch->update(['verified_by' => $this->approver->id]);

    expect(Gate::forUser($this->approver)->allows('approve', $this->batch))->toBeFalse();

    config(['sepu.maker_checker' => false]);

    expect(Gate::forUser($this->approver)->allows('approve', $this->batch))->toBeTrue();
})->group('migration');

it('lets a loan officer validate through the form and blocks an accountant', function () {
    $this->actingAs($this->officer)
        ->post(route('loan-migration.validate', $this->batch))
        ->assertSessionHasNoErrors();

    expect($this->batch->fresh()->status)->toBe(LoanMigrationBatchStatus::VALIDATED);

    $other = Scenario::migrationBatch($this->officer);

    $this->actingAs($this->accountant)
        ->post(route('loan-migration.validate', $other))
        ->assertForbidden();

    expect($other->fresh()->status)->toBe(LoanMigrationBatchStatus::DRAFT);
})->group('migration');

it('keeps a member out of the migration module', function () {
    $member = Scenario::member()->user;

    $this->actingAs($member)->post(route('loan-migration.validate', $this->batch));

    expect($this->batch->fresh()->status)->toBe(LoanMigrationBatchStatus::DRAFT);
})->group('migration');

it('requires a reason to reject a batch', function () {
    $this->workflow->validate($this->batch, $this->officer);
    $this->workflow->submit($this->batch, $this->officer);

    $this->actingAs($this->accountant)
        ->post(route('loan-migration.reject', $this->batch), ['reason' => ''])
        ->assertSessionHasErrors('reason');
})->group('migration');

it('lists the invalid records with their errors', function () {
    $this->batch->records()->first()->update(['outstanding_balance' => 20000]);
    $this->workflow->validate($this->batch, $this->officer);

    $this->actingAs($this->officer)
        ->get(route('loan-migration.validation-errors', $this->batch))
        ->assertInertia(fn ($page) => $page->has('records.data', 1));
})->group('migration');