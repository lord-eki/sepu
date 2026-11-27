<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanMigrationDecisionRequest extends FormRequest
{
    // Allows the request; the policy decides who may take each step.
    public function authorize(): bool
    {
        return true;
    }

    // Validates the optional remarks sent with a submit, verify or approve step.
    public function rules(): array
    {
        return [
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}