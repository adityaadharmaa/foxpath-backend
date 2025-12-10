<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UsersExportRequest extends FormRequest
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
            'format' => 'nullable|in:csv,excel,xlsx'
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Type mus be either siswa or mahasiswa',
            'format.in' => 'Format must be csv or excel'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'format' => $this->format ?? 'csv',
        ]);
    }
}
