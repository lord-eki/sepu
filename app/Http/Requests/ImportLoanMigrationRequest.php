<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportLoanMigrationRequest extends FormRequest
{
    // Allows the request; the policy decides who may import into a batch.
    public function authorize(): bool
    {
        return true;
    }

    // Validates the uploaded spreadsheet and the optional remarks.
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt,xls,xlsx', 'max:10240'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}