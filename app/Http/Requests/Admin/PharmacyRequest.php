<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PharmacyRequest extends FormRequest
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
        $pharmacyId = $this->route('pharmacy')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('pharmacies', 'email')->ignore($pharmacyId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'town' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'hospital_id' => ['nullable', 'string', 'exists:hospitals,id'],
            'radius_km' => ['nullable', 'integer', 'min:1', 'max:5'],
            'is_verified' => ['sometimes', 'boolean'],
            'is_open' => ['sometimes', 'boolean'],
        ];
    }
}
