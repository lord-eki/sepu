<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialSettingsRequest extends FormRequest
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
             'min_savings_balance' => ['required', 'numeric', 'min:0'],
            'max_savings_balance' => ['nullable', 'numeric', 'min:0'],
            'savings_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'savings_interest_calculation' => ['required', 'in:daily,monthly,quarterly,annually'],
            'savings_minimum_deposit' => ['required', 'numeric', 'min:0'],
            'savings_withdrawal_limit' => ['nullable', 'numeric', 'min:0'],
            'savings_withdrawal_fee' => ['nullable', 'numeric', 'min:0'],
            'share_value' => ['required', 'numeric', 'min:0'],
            'min_shares_required' => ['required', 'integer', 'min:1'],
            'max_shares_allowed' => ['nullable', 'integer', 'min:1'],
            'shares_transfer_fee' => ['nullable', 'numeric', 'min:0'],
            'shares_dividend_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'transaction_processing_fee' => ['nullable', 'numeric', 'min:0'],
            'mobile_money_charge' => ['nullable', 'numeric', 'min:0'],
            'bank_transfer_charge' => ['nullable', 'numeric', 'min:0'],
            'cheque_processing_fee' => ['nullable', 'numeric', 'min:0'],
            'daily_transaction_limit' => ['nullable', 'numeric', 'min:0'],
            'default_loan_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_loan_processing_fee' => ['nullable', 'numeric', 'min:0'],
            'default_loan_insurance_rate' => ['nullable', 'numeric', 'min:0'],
            'default_loan_penalty_rate' => ['nullable', 'numeric', 'min:0'],
            'loan_grace_period_days' => ['nullable', 'integer', 'min:0'],
            'max_loan_term_months' => ['nullable', 'integer', 'min:1'],
            'dividend_calculation_method' => ['required', 'in:shares_based,savings_based,combined'],
            'dividend_minimum_shares' => ['nullable', 'integer', 'min:0'],
            'dividend_processing_fee' => ['nullable', 'numeric', 'min:0'],
            'budget_variance_threshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'budget_approval_required' => ['boolean'],
            'budget_over_expenditure_allowed' => ['boolean']
        ];
    }
}
