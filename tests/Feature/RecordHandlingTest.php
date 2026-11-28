<?php

use App\Enums\LoanMigrationRecordValidationStatus;
use App\Models\AuditLog;
use App\Services\LoanMigration\LoanMigrationWorkflow;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\Scenario;

beforeEach(function () {
    $this->officer = Scenario::user('loan_officer');
    $this->accountant = Scenario::user('accountant');
    $this->batch = Scenario::migrationBatch($this->officer);
    $this->first = $this->batch->records()->orderBy('id')->first();
    $this->product = $this->first->loanProduct;
    $this->member = Scenario::member();
});

// Builds a valid record form payload, with any fields overridden.
function recordPayload($member, $product, array $override = []): array
{
    return array_merge([
        'member_id' => $member->id,
        'loan_product_id' => $product->id,
        'loan_number' => 'MAN-' . Str::upper(Str::random(6)),
        'original_loan_amount' => 10000,
        'date_disbursed' => '2026-01-01',
        'interest_rate' => 12,
        'repayment_period' => 12,
        'remaining_period' => 6,
        'outstanding_balance' => 6000,
        'total_amount_paid' => 4000,
        'last_repayment_date' => '2026-11-01',
        'next_due_date' => '2026-12-01',
        'loan_status' => 'active',
        'is_top_up' => false,
        'parent_loan_number' => null,
        'remarks' => null,
    ], $override);
}

it('adds a record by hand, validates it and refreshes the batch totals', function () {
    $this->actingAs($this->officer)
        ->post(route('loan-migration.records.store', $this->batch), recordPayload($this->member, $this->product))
        ->assertSessionHasNoErrors();

    $batch = $this->batch->fresh();

    expect($batch->total_records)->toBe(3)
        ->and($batch->valid_records)->toBe(1)
        ->and($batch->invalid_records)->toBe(0)
        ->and((float) $batch->total_original_amount)->toBe(30000.0)
        ->and($batch->records()->orderByDesc('id')->first()->validation_status)->toBe(LoanMigrationRecordValidationStatus::VALID);
})->group('migration');

it('rejects a record form with bad input', function (array $override, string $field) {
    $this->actingAs($this->officer)
        ->post(route('loan-migration.records.store', $this->batch), recordPayload($this->member, $this->product, $override))
        ->assertSessionHasErrors($field);

    expect($this->batch->records()->count())->toBe(2);
})->with([
    'negative amount' => [['original_loan_amount' => -5], 'original_loan_amount'],
    'restructured status' => [['loan_status' => 'restructured'], 'loan_status'],
    'future disbursement' => [['date_disbursed' => '2099-01-01'], 'date_disbursed'],
    'rate above 100' => [['interest_rate' => 150], 'interest_rate'],
    'unknown member' => [['member_id' => 999999], 'member_id'],
    'top up flag not boolean' => [['is_top_up' => 'maybe'], 'is_top_up'],
])->group('migration');

it('keeps a record that breaks a business rule but marks it invalid', function () {
    $this->actingAs($this->officer)
        ->post(route('loan-migration.records.store', $this->batch), recordPayload($this->member, $this->product, ['loan_number' => 'FLAG-1', 'outstanding_balance' => 20000]));

    $record = $this->batch->records()->where('loan_number', 'FLAG-1')->first();

    expect($record->validation_status)->toBe(LoanMigrationRecordValidationStatus::INVALID)
        ->and($record->validation_errors)->toHaveKey('outstanding_balance')
        ->and($this->batch->fresh()->invalid_records)->toBe(1);
})->group('migration');

it('blocks a loan number already used in the batch', function () {
    $this->actingAs($this->officer)
        ->post(route('loan-migration.records.store', $this->batch), recordPayload($this->member, $this->product, ['loan_number' => $this->first->loan_number]))
        ->assertSessionHasErrors('loan_number');

    expect($this->batch->records()->count())->toBe(2);
})->group('migration');

it('blocks changing a record to another record loan number in the batch', function () {
    $second = $this->batch->records()->orderByDesc('id')->first();

    $this->actingAs($this->officer)
        ->put(route('loan-migration.records.update', [$this->batch, $second]), recordPayload($second->member, $this->product, ['loan_number' => $this->first->loan_number]))
        ->assertSessionHasErrors('loan_number');
})->group('migration');

it('keeps a record whose loan number exists in another batch but marks it invalid', function () {
    $other = Scenario::migrationBatch($this->officer, 1);

    $this->actingAs($this->officer)
        ->post(route('loan-migration.records.store', $this->batch), recordPayload($this->member, $this->product, ['loan_number' => $other->records()->first()->loan_number]))
        ->assertSessionHasNoErrors();

    expect($this->batch->records()->orderByDesc('id')->first()->validation_errors)->toHaveKey('loan_number');
})->group('migration');

it('refuses changes once the batch is no longer editable', function () {
    $this->batch->update(['status' => 'submitted']);

    $this->actingAs($this->officer)
        ->post(route('loan-migration.records.store', $this->batch), recordPayload($this->member, $this->product))
        ->assertSessionHasErrors('batch');

    expect($this->batch->records()->count())->toBe(2);
})->group('migration');

it('limits record changes to staff who may capture', function () {
    $this->actingAs($this->accountant)
        ->post(route('loan-migration.records.store', $this->batch), recordPayload($this->member, $this->product))
        ->assertForbidden();

    $this->actingAs($this->member->user)
        ->post(route('loan-migration.records.store', $this->batch), recordPayload($this->member, $this->product));

    expect($this->batch->records()->count())->toBe(2);
})->group('migration');

it('marks a fixed record as corrected and audits the change', function () {
    $this->first->update(['outstanding_balance' => 20000]);
    app(LoanMigrationWorkflow::class)->validate($this->batch, $this->officer);

    $this->actingAs($this->officer)
        ->put(route('loan-migration.records.update', [$this->batch, $this->first]), recordPayload($this->first->member, $this->product, ['loan_number' => $this->first->loan_number]))
        ->assertSessionHasNoErrors();

    expect($this->first->fresh()->validation_status)->toBe(LoanMigrationRecordValidationStatus::CORRECTED)
        ->and($this->batch->fresh()->invalid_records)->toBe(0)
        ->and(AuditLog::where('action', 'loan_migration.record_updated')->count())->toBe(1);
})->group('migration');

it('will not update a record that belongs to another batch', function () {
    $other = Scenario::migrationBatch($this->officer, 1);
    $foreign = $other->records()->first();

    $this->actingAs($this->officer)
        ->put(route('loan-migration.records.update', [$this->batch, $foreign]), recordPayload($foreign->member, $this->product, ['loan_number' => 'HIJACK-1']))
        ->assertSessionHasErrors('batch');

    expect($foreign->fresh()->loan_number)->not->toBe('HIJACK-1');
})->group('migration');

it('deletes a record, refreshes the totals and audits it', function () {
    $this->actingAs($this->officer)
        ->delete(route('loan-migration.records.destroy', [$this->batch, $this->first]))
        ->assertSessionHasNoErrors();

    expect($this->batch->records()->count())->toBe(1)
        ->and($this->batch->fresh()->total_records)->toBe(1)
        ->and(AuditLog::where('action', 'loan_migration.record_removed')->count())->toBe(1);
})->group('migration');

it('lists records and filters them by validation status', function () {
    $this->first->update(['validation_status' => 'invalid']);

    $this->actingAs($this->officer)
        ->get(route('loan-migration.records.index', [$this->batch, 'validation' => 'invalid']))
        ->assertInertia(fn($page) => $page->has('records.data', 1));

    $this->actingAs($this->officer)
        ->get(route('loan-migration.records.index', [$this->batch, 'validation' => 'nonsense']))
        ->assertSessionHasErrors('validation');
})->group('migration');

it('opens the edit form with the record and the choices', function () {
    $this->actingAs($this->officer)
        ->get(route('loan-migration.records.edit', [$this->batch, $this->first]))
        ->assertInertia(fn($page) => $page->where('record.id', $this->first->id)->has('loanProducts')->has('members'));
})->group('migration');

it('downloads the import template for staff only', function () {
    $this->actingAs($this->accountant)
        ->get(route('loan-migration.template.download'))
        ->assertDownload('loan-migration-template.xlsx');

    $response = $this->actingAs($this->member->user)->get(route('loan-migration.template.download'));

    expect($response->getStatusCode())->not->toBe(200);
})->group('migration');

it('exports the batch with text stored as text', function () {
    $this->first->update(['remarks' => '=HYPERLINK("http://example.test")']);

    $response = $this->actingAs($this->officer)->get(route('loan-migration.export', $this->batch));
    $response->assertDownload();

    $path = tempnam(sys_get_temp_dir(), 'exp') . '.xlsx';
    file_put_contents($path, $response->streamedContent());
    $sheet = IOFactory::load($path)->getActiveSheet();

    expect($sheet->getHighestRow())->toBe(3)
        ->and($sheet->getCell('C2')->getValue())->toBe($this->first->loan_number)
        ->and($sheet->getCell('P2')->getValue())->toBe('=HYPERLINK("http://example.test")')
        ->and($sheet->getCell('P2')->getDataType())->toBe(DataType::TYPE_STRING);
})->group('migration');

it('previews an upload without saving anything', function () {
    $header = 'member_number,loan_product,loan_number,original_loan_amount,date_disbursed,interest_rate,repayment_period,remaining_period,outstanding_balance,total_amount_paid,last_repayment_date,next_due_date,loan_status,is_top_up,parent_loan_number,remarks';
    $good = "{$this->member->membership_id},{$this->product->code},PREV-1,10000,2026-01-01,12,12,6,6000,4000,2026-11-01,2026-12-01,active,no,,";
    $bad = "NOPE,{$this->product->code},PREV-2,10000,2026-01-01,12,12,6,6000,4000,2026-11-01,2026-12-01,active,no,,";
    $file = UploadedFile::fake()->createWithContent('rows.csv', implode("\n", [$header, $good, $bad]));

    $this->actingAs($this->officer)
        ->post(route('loan-migration.import.preview', $this->batch), ['file' => $file])
        ->assertOk()
        ->assertJsonPath('total', 2)
        ->assertJsonPath('valid', 1)
        ->assertJsonPath('invalid', 1);

    expect($this->batch->records()->count())->toBe(2);
})->group('migration');

it('keeps the import preview to staff who may capture', function () {
    $file = UploadedFile::fake()->createWithContent('rows.csv', 'member_number');

    $this->actingAs($this->accountant)
        ->post(route('loan-migration.import.preview', $this->batch), ['file' => $file])
        ->assertForbidden();
})->group('migration');
