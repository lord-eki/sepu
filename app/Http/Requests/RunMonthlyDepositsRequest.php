<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunMonthlyDepositsRequest extends FormRequest
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
            'entries.*.member_id' => ['required', 'integer', 'exists:members,id'],
        ];
    }
}
