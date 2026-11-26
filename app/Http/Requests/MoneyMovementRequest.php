<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class MoneyMovementRequest extends FormRequest
{
    public const PAYMENT_METHODS = ['cash', 'mobile_money', 'bank_transfer', 'cheque'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'payment_method' => ['required', Rule::in(self::PAYMENT_METHODS)],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}