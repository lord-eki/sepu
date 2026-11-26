<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingRequest extends FormRequest
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
            'sms_enabled' => ['boolean'],
            'sms_provider' => ['nullable', 'string', 'in:africastalking,twilio,custom'],
            'sms_api_key' => ['nullable', 'string'],
            'sms_api_secret' => ['nullable', 'string'],
            'sms_sender_id' => ['nullable', 'string', 'max:11'],
            'sms_balance_threshold' => ['nullable', 'numeric', 'min:0'],
            'email_enabled' => ['boolean'],
            'email_driver' => ['nullable', 'string', 'in:smtp,mailgun,ses,postmark'],
            'email_host' => ['nullable', 'string'],
            'email_port' => ['nullable', 'integer'],
            'email_username' => ['nullable', 'string'],
            'email_password' => ['nullable', 'string'],
            'email_encryption' => ['nullable', 'string', 'in:tls,ssl'],
            'email_from_address' => ['nullable', 'email'],
            'email_from_name' => ['nullable', 'string'],
            'push_enabled' => ['boolean'],
            'push_server_key' => ['nullable', 'string'],
            'push_vapid_public_key' => ['nullable', 'string'],
            'push_vapid_private_key' => ['nullable', 'string'],
            'notify_on_deposit' => ['boolean'],
            'notify_on_withdrawal' => ['boolean'],
            'notify_on_loan_approval' => ['boolean'],
            'notify_on_loan_disbursement' => ['boolean'],
            'notify_on_loan_repayment' => ['boolean'],
            'notify_on_dividend_payment' => ['boolean'],
            'notify_on_account_activation' => ['boolean'],
            'notify_on_account_suspension' => ['boolean'],
            'notify_on_password_change' => ['boolean'],
            'notify_on_loan_arrears' => ['boolean'],
            'arrears_notification_frequency' => ['nullable', 'in:daily,weekly,monthly'],
            'notification_batch_size' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'notification_retry_attempts' => ['nullable', 'integer', 'min:1', 'max:10'],
            'notification_retry_delay' => ['nullable', 'integer', 'min:1']
        ];
    }
}
