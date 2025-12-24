<?php

namespace App\Http\Requests\ProfileEducation;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileEducation extends FormRequest
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
            'level' => 'required|in:siswa,mahasiswa',
            'institution_name' => 'required|string|max:255',
            'major' => 'nullable|string|max:255',

            'nisn' => 'nullable|string|max:30',
            'nim' => 'nullable|string|max:30',

            'gpa' => 'nullable|required_if:level,mahasiswa|numeric|min:0|max:4',
            'average_score' => 'nullable|required_if:level,siswa|numeric|min:0|max:100',

            'is_active' => 'sometimes|boolean'
        ];
    }
}
