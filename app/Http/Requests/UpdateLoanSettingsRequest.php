<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLoanSettingsRequest extends FormRequest
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
            'loan_application_fee' => ['nullable', 'numeric', 'min:0'],
            'loan_appraisal_fee' => ['nullable', 'numeric', 'min:0'],
            'loan_processing_time_days' => ['nullable', 'integer', 'min:1'],
            'loan_disbursement_delay_days' => ['nullable', 'integer', 'min:0'],
            'max_loan_amount' => ['nullable', 'numeric', 'min:0'],
            'loan_to_savings_ratio' => ['nullable', 'numeric', 'min:1'],
            'loan_to_shares_ratio' => ['nullable', 'numeric', 'min:1'],
            'guarantor_required' => ['boolean'],
            'min_guarantors_required' => ['nullable', 'integer', 'min:0'],
            'max_guarantors_allowed' => ['nullable', 'integer', 'min:1'],
            'guarantor_loan_limit_ratio' => ['nullable', 'numeric', 'min:0'],
            'auto_calculate_repayment' => ['boolean'],
            'allow_loan_restructuring' => ['boolean'],
            'loan_restructuring_fee' => ['nullable', 'numeric', 'min:0'],
            'early_repayment_penalty' => ['nullable', 'numeric', 'min:0'],
            'late_payment_grace_days' => ['nullable', 'integer', 'min:0'],
            'arrears_notification_days' => ['nullable', 'integer', 'min:1'],
            'loan_writeoff_days' => ['nullable', 'integer', 'min:1'],
            'compound_interest' => ['boolean'],
            'interest_calculation_method' => ['required', 'in:reducing_balance,flat_rate,compound'],
            'repayment_frequency' => ['required', 'in:weekly,bi_weekly,monthly,quarterly']
        ];
    }
}
