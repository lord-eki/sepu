<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunLoanRepaymentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.repayment_id' => ['required', 'integer', 'exists:loan_repayments,id'],
            'entries.*.deduct_amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
        ];
    }
}