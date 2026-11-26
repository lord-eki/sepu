<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSecuritySettingRequest extends FormRequest
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
            'password_min_length' => ['required', 'integer', 'min:6', 'max:50'],
            'password_require_uppercase' => ['boolean'],
            'password_require_lowercase' => ['boolean'],
            'password_require_numbers' => ['boolean'],
            'password_require_symbols' => ['boolean'],
            'password_expiry_days' => ['nullable', 'integer', 'min:1'],
            'password_history_count' => ['nullable', 'integer', 'min:0'],
            'max_login_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'lockout_duration_minutes' => ['required', 'integer', 'min:1'],
            'session_timeout_minutes' => ['required', 'integer', 'min:5'],
            'require_2fa' => ['boolean'],
            'force_2fa_for_admin' => ['boolean'],
            'otp_expiry_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'api_rate_limit' => ['required', 'integer', 'min:1'],
            'api_rate_limit_window' => ['required', 'integer', 'min:1'],
            'api_token_expiry_days' => ['nullable', 'integer', 'min:1'],
            'api_require_https' => ['boolean'],
            'audit_log_retention_days' => ['nullable', 'integer', 'min:1'],
            'audit_sensitive_actions' => ['boolean'],
            'audit_failed_logins' => ['boolean'],
            'audit_data_changes' => ['boolean'],
            'ip_whitelist_enabled' => ['boolean'],
            'ip_whitelist' => ['nullable', 'string'],
            'ip_blacklist_enabled' => ['boolean'],
            'ip_blacklist' => ['nullable', 'string'],
            'encrypt_sensitive_data' => ['boolean'],
            'backup_encryption_enabled' => ['boolean'],
            'file_upload_scan_enabled' => ['boolean'],
            'max_file_upload_size' => ['nullable', 'integer', 'min:1']
        ];
    }
}
