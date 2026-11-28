<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class LoanMigrationRecordRequest extends FormRequest
{
    // Allows the request; the policy decides who may change migration records.
    public function authorize(): bool
    {
        return true;
    }

    // Validates the fields of a single legacy loan; cross-field checks are done by the record validator.
    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'loan_product_id' => ['required', 'integer', 'exists:loan_products,id'],
            'loan_number' => ['required', 'string', 'max:50', Rule::unique('loan_migration_records', 'loan_number')
                ->where('batch_id', $this->route('batch')?->id)->ignore($this->route('record')?->id),],
            'original_loan_amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'date_disbursed' => ['required', 'date', 'before_or_equal:today'],
            'interest_rate' => ['required', 'numeric', 'between:0,100'],
            'repayment_period' => ['required', 'integer', 'between:1,600'],
            'remaining_period' => ['required', 'integer', 'between:0,600'],
            'outstanding_balance' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'total_amount_paid' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'last_repayment_date' => ['nullable', 'date'],
            'next_due_date' => ['nullable', 'date'],
            'loan_status' => ['required', Rule::in(['active', 'cleared'])],
            'is_top_up' => ['required', 'boolean'],
            'parent_loan_number' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    // Gives the duplicate loan number rule a clear message.
    public function messages(): array
    {
        return [
            'loan_number.unique' => 'This loan number already exists in this batch.',
        ];
    }
}
