<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpandOrderSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'radius_km' => ['required', 'integer', 'min:2', 'max:100'],
        ];
    }
}
