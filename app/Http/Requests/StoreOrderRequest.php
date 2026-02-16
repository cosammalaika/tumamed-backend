<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hospital_id' => ['required', 'string', 'exists:hospitals,id'],
            'user_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'user_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'is_self_patient' => ['required', 'boolean'],
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_phone' => ['required', 'string', 'max:32'],
            'search_radius_km' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
