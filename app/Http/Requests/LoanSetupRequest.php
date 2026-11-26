<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoanSetupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'interest_rate'       => 'nullable|numeric|min:0|max:100',
            'processing_fee_rate' => 'nullable|numeric|min:0|max:100',
            'insurance_rate'      => 'nullable|numeric|min:0|max:100',
            'processing_fee_flat' => 'nullable|numeric|min:0',
            'min_amount'          => 'nullable|numeric|min:0',
            'max_amount'          => 'nullable|numeric|gte:min_amount',
            'min_term_months'     => 'nullable|integer|min:1',
            'max_term_months'     => 'nullable|integer|gte:min_term_months',
        ];
    }
}
