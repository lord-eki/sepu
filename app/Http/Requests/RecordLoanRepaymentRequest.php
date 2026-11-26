<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordLoanRepaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'source' => ['required', Rule::in(['deposits', 'cash', 'mobile_money', 'bank_transfer', 'cheque'])],
            'request_key' => ['required', 'uuid'],
            'reference' => ['nullable', 'string', 'max:100'],
        ];
    }
}