<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendBulkRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => ['required', Rule::in(['transaction', 'loan', 'dividend', 'general', 'system'])],
            'channels' => 'required|array|min:1',
            'channels.*' => Rule::in(['sms', 'email', 'system']),
            'recipient_group' => 'required|string',
            'custom_recipients' => 'nullable|array',
            'custom_recipients.*' => 'exists:users,id',
            'schedule_at' => 'nullable|date|after:now',
        ];
    }
}
