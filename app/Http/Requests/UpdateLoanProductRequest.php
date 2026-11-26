<?php

namespace App\Http\Requests;

use App\Models\LoanProduct;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLoanProductRequest extends FormRequest
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
    public function rules( LoanProduct $loanProduct): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:loan_products,code,' . $loanProduct->id,
            'description' => 'required|string',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'min_term_months' => 'required|integer|min:1',
            'max_term_months' => 'required|integer|gt:min_term_months',
            'processing_fee_rate' => 'required|numeric|min:0|max:100',
            'insurance_rate' => 'required|numeric|min:0|max:100',
            'grace_period_days' => 'required|integer|min:0',
            'penalty_rate' => 'required|numeric|min:0|max:100',
            'eligibility_criteria' => 'required|array',
            'required_documents' => 'required|array',
            'requires_guarantor' => 'required|boolean',
            'min_guarantors' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ];
    }
}
