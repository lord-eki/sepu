<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
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
            'member_id' => 'required|exists:members,id',
            'loan_product_id' => 'required|exists:loan_products,id',
            'applied_amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'guarantors' => 'required|array|min:1',
            'guarantors.*.member_id' => 'required|exists:members,id',
            'guarantors.*.guaranteed_amount' => 'required|numeric|min:1',
            'documents'   => 'sometimes|array|max:10',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }
}
