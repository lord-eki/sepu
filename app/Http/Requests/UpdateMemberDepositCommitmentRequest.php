<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberDepositCommitmentRequest extends FormRequest
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
            'monthly_amount'   => 'required|numeric|min:0',
            'principal_amount' => 'nullable|numeric|min:0',
            'interest_amount'  => 'nullable|numeric|min:0',
            'deduction_day'    => 'nullable|integer|min:1|max:28',
            'effective_to'     => 'nullable|date',
            'is_active'        => 'boolean',
            'dividend_mode'    => 'nullable|in:reinvest,payout',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
