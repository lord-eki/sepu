<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentVoucherRequest extends FormRequest
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
            'voucher_type' => 'required|in:loan_disbursement,operational_expense,dividend_payment,refund,other',
            'payee_name' => 'required|string|max:255',
            'payee_phone' => 'nullable|string|max:20',
            'payee_account' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'purpose' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget_item_id' => 'nullable|exists:budget_items,id',
            'loan_id' => 'nullable|exists:loans,id',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ];
    }
}
