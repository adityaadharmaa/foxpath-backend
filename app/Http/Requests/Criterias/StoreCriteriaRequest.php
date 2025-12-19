<?php

namespace App\Http\Requests\Criterias;

use Illuminate\Foundation\Http\FormRequest;

class StoreCriteriaRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:10', 'unique:criterias,code'],
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'between:0,1'],
            'type' => ['required', 'in:benefit,cost'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'weight.between' => 'Weight must be between 0 and 1.',
        ];
    }
}
