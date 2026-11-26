<?php

namespace App\Http\Requests;

use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetRequest extends FormRequest
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
    public function rules(Budget $budget): array
    {
        return [
            'budget_year'  => ['required', 'integer', 'min:' . Carbon::now()->year, 'max:' . (Carbon::now()->year + 5), Rule::unique('budgets', 'budget_year')->ignore($budget->id),],
            'title'        => ['required', 'string|max:255'],
            'description'  => ['nullable', 'string'],
            'total_budget' => ['required', 'numeric|min:0'],
            'start_date'   => ['required', 'date'],
            'end_date'     => ['required', 'date|after:start_date'],
            'budget_items' => ['required', 'array|min:1'],
            'budget_items.*.chart_of_account_id' => ['required', 'exists:charts_of_accounts,id'],
            'budget_items.*.description'          => ['nullable', 'string'],
            'budget_items.*.budgeted_amount'      => ['required', 'numeric|min:0'],
        ];
    }
}
