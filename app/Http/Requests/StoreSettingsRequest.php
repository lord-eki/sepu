<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettingsRequest extends FormRequest
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
            'sacco_name' => ['nullable', 'string', 'max:255'],
            'sacco_code' => ['nullable', 'string', 'max:10', 'alpha_num'],
            'registration_number' => ['nullable', 'string', 'max:50'],
            'sacco_address' => ['nullable', 'string', 'max:500'],
            'sacco_phone' => ['nullable', 'string', 'max:20'],
            'sacco_email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'language' => ['nullable', 'string', 'max:10'],
            'date_format' => ['nullable', 'string', 'max:20'],
            'time_format' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
            'maintenance_mode' => ['nullable', 'boolean'],
            'member_registration_open' => ['nullable', 'boolean'],
            'auto_generate_member_id' => ['nullable', 'boolean'],
            'require_email_verification' => ['nullable', 'boolean'],
            'require_phone_verification' => ['nullable', 'boolean'],
        ];
    }
}
