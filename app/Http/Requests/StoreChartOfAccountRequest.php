<?php

namespace App\Http\Requests;

use App\Support\AccountTypes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreChartOfAccountRequest extends FormRequest
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
            'account_code'      => ['required','string','max:20','unique:charts_of_accounts,account_code'],
            'account_name'      => ['required','string','max:255'],
            'account_type'      => ['required','in:' . implode(',', array_keys(AccountTypes::ACCOUNT_TYPES))],
            'account_category'  => ['required','string','max:60','exists:account_categories,key'],
            'normal_balance'    => ['required','in:debit,credit'],
            'parent_account_id' => ['nullable','exists:charts_of_accounts,id'],
            'description'       => ['nullable','string'],
            'opening_balance'   => ['nullable','numeric','min:0'],
            'is_active'         => ['boolean'],
        ];
    }
}
