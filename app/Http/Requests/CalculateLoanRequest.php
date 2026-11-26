<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CalculateLoanRequest extends FormRequest
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
            'loan_product_id'    => ['required','exists:loan_products,id'],
            'principal_amount'   => ['required','numeric','min:1'],
            'term_months'        => ['required','integer','min:1'],
            'member_id'          => ['nullable','exists:members,id'],
        ];
    }
}
