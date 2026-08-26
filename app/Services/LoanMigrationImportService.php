<?php

namespace App\Services;

use App\Models\LoanMigrationBatch;
use App\Models\LoanMigrationRecord;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Enums\LoanMigrationProcessingStatus;
use App\Enums\LoanMigrationRecordValidationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LoanMigrationImportService
{

    public function import(Request $request, LoanMigrationBatch $batch , bool $canCreateBatch): RedirectResponse
    {
        $user = Auth::user();

        abort_unless(
            $user && $canCreateBatch,
            403
        );

        abort_unless($batch->isEditable(), 422);

        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:csv,xls,xlsx',
                'max:10240',
            ],
            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $file = $validated['file'];

        $spreadsheetRows = $this->readSpreadsheet($file);

        $rows = $this->normalizeRows($spreadsheetRows);

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => [
                    'The uploaded file does not contain any records.',
                ],
            ]);
        }

        $this->validateHeadings($rows);

        $records = [];
        $loanNumbers = [];

        foreach ($rows as $row) {
            $rowIsEmpty = collect($row)
                ->except('_row_number')
                ->filter(fn($value) => $value !== null && $value !== '')
                ->isEmpty();

            if ($rowIsEmpty) {
                continue;
            }

            $validation = $this->validateMigrationRow(
                $row,
                $batch,
                $loanNumbers
            );

            $errors = $validation['errors'];

            $records[] = [
                'batch_id' => $batch->id,

                'member_id' => $validation['member']?->id,
                'member_number' => trim(
                    (string) ($row['member_number'] ?? '')
                ),

                'loan_number' => trim(
                    (string) ($row['loan_number'] ?? '')
                ),

                'loan_product_id' => $validation['loan_product']?->id,

                'original_loan_amount' =>
                $row['original_loan_amount'] ?? 0,

                'date_disbursed' => $this->parseSpreadsheetDate(
                    $row['date_disbursed'] ?? null
                ),

                'interest_rate' =>
                $row['interest_rate'] ?? 0,

                'repayment_period' =>
                $row['repayment_period'] ?? 0,

                'remaining_period' =>
                $row['remaining_period'] ?? 0,

                'outstanding_balance' =>
                $row['outstanding_balance'] ?? 0,

                'total_amount_paid' =>
                $row['total_amount_paid'] ?? 0,

                'last_repayment_date' => $this->parseSpreadsheetDate(
                    $row['last_repayment_date'] ?? null
                ),

                'next_due_date' => $this->parseSpreadsheetDate(
                    $row['next_due_date'] ?? null
                ),

                'loan_status' => strtolower(
                    trim((string) ($row['loan_status'] ?? ''))
                ),

                'is_top_up' => $validation['is_top_up'],

                'parent_loan_number' => !empty($row['parent_loan_number'] ?? null)
                    ? trim((string) $row['parent_loan_number'])
                    : null,

                'remarks' => !empty($row['remarks'] ?? null)
                    ? trim((string) $row['remarks'])
                    : $validated['remarks'] ?? null,

                'validation_status' => empty($errors)
                    ? LoanMigrationRecordValidationStatus::VALID
                    : LoanMigrationRecordValidationStatus::INVALID,

                'validation_errors' => empty($errors)
                    ? null
                    : $errors,

                'processing_status' =>
                LoanMigrationProcessingStatus::PENDING,
            ];
        }

        if ($records === []) {
            throw ValidationException::withMessages([
                'file' => [
                    'The uploaded file does not contain any usable records.',
                ],
            ]);
        }

        DB::transaction(function () use ($batch, $records) {
            foreach ($records as $record) {
                LoanMigrationRecord::create($record);
            }

            $this->recalculateBatchTotals($batch);
        });

        return redirect()
            ->route('loan-migration.show', $batch)
            ->with(
                'success',
                count($records) . ' loan records imported successfully.'
            );
    }


    private function readSpreadsheet(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();

        return $worksheet->toArray(null, true, true, true);
    }

    private function normalizeHeading(?string $heading): string
    {
        return str($heading)->lower()->trim()->replace([' ', '_', '/'], '_')->toString();
    }


    private function normalizeRows(array $rows): array
    {
        $headingRow = array_shift($rows);
        $headings = collect($headingRow)->mapWithKeys(function ($heading, $column) {
            return [$column => $this->normalizeHeading($heading)];
        })->filter()->all();

        return collect($rows)->map(function ($row, $index) use ($headings) {
            $normalized = [];

            foreach ($headings as $column => $heading) {
                $normalized[$heading] = $row[$column] ?? null;
            }
            $normalized['_row_number'] = $index + 2;
            return $normalized;
        })->values()->all();
    }

    private function validateHeadings(array $rows): void
    {
        $required = ['member_number', 'loan_product', 'loan_number', 'original_loan_amount', 'date_disbursed', 'interest_rate', 'repayment_period', 'remaining_period', 'outstanding_balance', 'total_amount_paid', 'loan_status', 'is_top_up'];

        $available = array_keys($rows[0] ?? []);
        $missing = array_diff($required, $available);

        if ($missing !== []) {
            throw  ValidationException::withMessages(['file' => 'Missing required columns: ' . implode(',', $missing)]);
        }
    }


    private function validateMigrationRow(array $row, LoanMigrationBatch $batch, array &$loanNumbers): array
    {
        $errors = [];

        $member = Member::where(
            'membership_id',
            trim((string) $row['member_number'])
        )->first();

        if (!$member) {
            $errors['member_number'][] =
                'Member does not exist.';
        }

        $loanProduct = LoanProduct::query()
            ->where('code', trim((string) $row['loan_product']))
            ->orWhere('name', trim((string) $row['loan_product']))
            ->first();

        if (!$loanProduct) {
            $errors['loan_product'][] =
                'Loan product does not exist.';
        }

        $loanNumber = trim((string) $row['loan_number']);

        if ($loanNumber === '') {
            $errors['loan_number'][] =
                'Loan number is required.';
        }

        if (isset($loanNumbers[$loanNumber])) {
            $errors['loan_number'][] =
                'Loan number is duplicated in this file.';
        }

        if (
            LoanMigrationRecord::where('batch_id', $batch->id)
            ->where('loan_number', $loanNumber)
            ->exists()
        ) {
            $errors['loan_number'][] =
                'Loan number already exists in this batch.';
        }

        if (
            !is_numeric($row['original_loan_amount']) ||
            $row['original_loan_amount'] < 0
        ) {
            $errors['original_loan_amount'][] =
                'Original loan amount must be a valid amount.';
        }

        if (
            !is_numeric($row['outstanding_balance']) ||
            $row['outstanding_balance'] < 0
        ) {
            $errors['outstanding_balance'][] =
                'Outstanding balance must be a valid amount.';
        }

        if (
            !is_numeric($row['total_amount_paid']) ||
            $row['total_amount_paid'] < 0
        ) {
            $errors['total_amount_paid'][] =
                'Total amount paid must be a valid amount.';
        }

        $isTopUp = filter_var(
            $row['is_top_up'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        if ($isTopUp && empty($row['parent_loan_number'])) {
            $errors['parent_loan_number'][] =
                'Parent loan number is required for a top-up.';
        }

        $loanNumbers[$loanNumber] = true;

        return [
            'errors' => $errors,
            'member' => $member,
            'loan_product' => $loanProduct,
            'is_top_up' => $isTopUp,
        ];
    }


    private function recalculateBatchTotals(LoanMigrationBatch $batch): void
    {
        $records = $batch->records();

        $batch->update([
            'total_records' => $records->count(),

            'valid_records' => $batch->records()
                ->whereIn('validation_status', [
                    LoanMigrationRecordValidationStatus::VALID->value,
                    LoanMigrationRecordValidationStatus::CORRECTED->value,
                ])
                ->count(),

            'invalid_records' => $batch->records()
                ->where(
                    'validation_status',
                    LoanMigrationRecordValidationStatus::INVALID->value
                )
                ->count(),

            'total_original_amount' => $batch->records()
                ->sum('original_loan_amount'),

            'total_amount_paid' => $batch->records()
                ->sum('total_amount_paid'),

            'total_outstanding_balance' => $batch->records()
                ->sum('outstanding_balance'),
        ]);
    }

    private function parseSpreadsheetDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date
                ::excelToDateTimeObject($value)
                ->format('Y-m-d');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
