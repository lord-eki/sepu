<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMemberDepositCommitmentRequest extends FormRequest
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
             'type' => 'required|in:contribution,loan_repayment,dividend',
            'account_id' => 'nullable|exists:accounts,id',
            'loan_id' => 'nullable|exists:loans,id',
            'monthly_amount'   => 'required|numeric|min:0',
            'principal_amount'  => 'nullable|numeric|min:0',
            'interest_amount'   => 'nullable|numeric|min:0',
            'deduction_day'    => 'nullable|integer|min:1|max:28',
            'dividend_mode'    => 'nullable|in:reinvest,payout',
            'effective_from'   => 'required|date',
            'effective_to'     => 'nullable|date|after:effective_from',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
