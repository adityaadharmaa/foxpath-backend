<?php

namespace App\Http\Requests\Criterias;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCriteriaRequest extends FormRequest
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
            'code' => [
                'sometimes',
                'string',
                'max:10',
                Rule::unique('criterias', 'code')->ignore($this->route('criteria')),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'weight' => ['sometimes', 'numeric', 'between:0,1'],
            'type' => ['sometimes', 'in:benefit,cost'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
