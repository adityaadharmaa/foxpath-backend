<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UsersIndexRequest extends FormRequest
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
            'type' => 'nullable|in:siswa,mahasiswa',
            'per_page' => 'nullable|integer|min:1|max:150' 
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'User type must be either siswa or mahasiswa.',
            'per_page.integer' => 'Per page must be a number.', 
            'per_page.min' => 'Per page must be at least 1.', 
            'per_page.max' => 'Per page cannot exceed 200.', 
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'per_page' => $this->per_page ?? 15,
        ]);
    }
}
