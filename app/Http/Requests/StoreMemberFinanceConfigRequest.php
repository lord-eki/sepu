<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMemberFinanceConfigRequest extends FormRequest
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
            'contribution_active'     => 'boolean',
            'monthly_contribution'    => 'numeric|min:0',
            'contribution_account_id' => 'nullable|exists:accounts,id',
            'loan_auto_deduct'        => 'boolean',
            'loan_deduction_amount'   => 'nullable|numeric|min:0',
            'dividend_eligible'       => 'boolean',
            'dividend_account_id'     => 'nullable|exists:accounts,id',
        ];
    }
}
