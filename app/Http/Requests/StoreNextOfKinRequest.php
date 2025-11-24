<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNextOfKinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // allow all authenticated users unless you restrict
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'id_number' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'allocation_percentage' => 'nullable|numeric|min:0|max:100',
            'is_primary' => 'nullable|boolean',
        ];
        }
}
