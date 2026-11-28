<?php

namespace App\Http\Requests;

use App\Enums\LoanMigrationProcessingStatus;
use App\Enums\LoanMigrationRecordValidationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoanMigrationRecordFilterRequest extends FormRequest
{
    // Allows the request; the policy decides who may open the module.
    public function authorize(): bool
    {
        return true;
    }

    // Validates the optional filters on the records list.
    public function rules(): array
    {
        return [
            'validation' => ['nullable', Rule::enum(LoanMigrationRecordValidationStatus::class)],
            'processing' => ['nullable', Rule::enum(LoanMigrationProcessingStatus::class)],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}