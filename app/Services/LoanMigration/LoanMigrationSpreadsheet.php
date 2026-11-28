<?php

namespace App\Services\LoanMigration;

use App\Models\LoanMigrationBatch;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoanMigrationSpreadsheet
{
    private const HEADINGS = [
        'member_number', 'loan_product', 'loan_number', 'original_loan_amount', 'date_disbursed',
        'interest_rate', 'repayment_period', 'remaining_period', 'outstanding_balance', 'total_amount_paid',
        'last_repayment_date', 'next_due_date', 'loan_status', 'is_top_up', 'parent_loan_number', 'remarks',
    ];

    private const EXAMPLE = [
        'SEP000001', 'PRODUCT-CODE', 'LN-0001', 50000, '2026-01-15',
        12, 24, 14, 28000, 22000,
        '2026-10-15', '2026-11-15', 'active', 'no', '', '',
    ];

    // Streams an import template with the headings the importer expects and one example row.
    public function template(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->writeRow($sheet, 1, self::HEADINGS);
        $this->writeRow($sheet, 2, self::EXAMPLE);
        $sheet->getStyle('A1:P1')->getFont()->setBold(true);

        return $this->download($spreadsheet, 'loan-migration-template.xlsx');
    }

    // Streams every record of a batch with its validation and processing result.
    public function export(LoanMigrationBatch $batch): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->writeRow($sheet, 1, array_merge(self::HEADINGS, ['validation_status', 'validation_errors', 'processing_status', 'created_loan_number']));
        $sheet->getStyle('A1:T1')->getFont()->setBold(true);

        $row = 2;

        foreach ($batch->records()->with(['loanProduct', 'loan'])->orderBy('id')->get() as $record) {
            $this->writeRow($sheet, $row++, [
                $record->member_number,
                $record->loanProduct?->code,
                $record->loan_number,
                (float) $record->original_loan_amount,
                $record->date_disbursed?->toDateString(),
                (float) $record->interest_rate,
                (int) $record->repayment_period,
                (int) $record->remaining_period,
                (float) $record->outstanding_balance,
                (float) $record->total_amount_paid,
                $record->last_repayment_date?->toDateString(),
                $record->next_due_date?->toDateString(),
                $record->loan_status,
                $record->is_top_up ? 'yes' : 'no',
                $record->parent_loan_number,
                $record->remarks,
                $record->validation_status?->value,
                $this->errorText($record->validation_errors),
                $record->processing_status?->value,
                $record->loan?->loan_number,
            ]);
        }

        return $this->download($spreadsheet, "loan-migration-{$batch->batch_number}.xlsx");
    }

    // Writes one row, storing text as text so a value starting with = is never run as a formula.
    private function writeRow(Worksheet $sheet, int $row, array $values): void
    {
        foreach (array_values($values) as $index => $value) {
            $cell = $sheet->getCell([$index + 1, $row]);

            if (is_int($value) || is_float($value)) {
                $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);
            } else {
                $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            }
        }
    }

    // Flattens a record's field => messages errors into one readable cell.
    private function errorText(?array $errors): string
    {
        if (! $errors) {
            return '';
        }

        return collect($errors)->map(fn ($messages, $field) => $field . ': ' . implode(' ', (array) $messages))->implode(' | ');
    }

    // Streams the spreadsheet to the browser as an xlsx download.
    private function download(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}