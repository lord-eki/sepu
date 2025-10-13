<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // "user_id" => "required|exists:users,id",
            // "membership_id" => "required|exists:memberships,id",
            "first_name" => "required|string|max:255",
            "last_name" => "required|string|max:255",
            "middle_name" => "nullable|string|max:255",
            "id_number" => "required|string|max:50|unique:members,id_number",
            "id_type" => "required|string|max:50",
            "date_of_birth" => "required|date|before:today",
            "gender" => "required|string",
            "marital_status" => "nullable|string|max:50",
            "occupation" => "nullable|string|max:255",
            "employer" => "nullable|string|max:255",
            "monthly_income" => "nullable|numeric|min:0",
            "physical_address" => "nullable|string|max:255",
            "postal_address" => "nullable|string|max:255",
            "city" => "nullable|string|max:100",
            "county" => "nullable|string|max:100",
            "country" => "nullable|string|max:100",
            "emergency_contact_name" => "nullable|string|max:255",
            "emergency_contact_phone" => "nullable|string|max:20",
            "emergency_contact_relationship" => "nullable|string|max:100",
            // "membership_status" => "required|string|in:active,inactive,suspended",
            // "membership_date" => "required|date",
            "profile_photo" => "nullable|image|max:2048",
            "documents" => "nullable|array",
            "documents.*" => "file|mimes:pdf,jpg,jpeg,png|max:2048",
        ];
    }
}
