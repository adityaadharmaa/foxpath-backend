<?php

namespace App\Http\Requests\Criterias;

use Illuminate\Foundation\Http\FormRequest;

class CriteriaIndexRequest extends FormRequest
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
            'q' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
