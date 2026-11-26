<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'recipient_member_id' => [
                'required',
                'integer',
                Rule::exists('members', 'id')->where('membership_status', 'active'),
                Rule::notIn([$this->route('account')?->member_id]),
            ],
        ];
    }
}