<?php

namespace App\Http\Requests\ProfileEducation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileEducationRequest extends FormRequest
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
            'institution_name' => 'sometimes|string|max:255',
            'major' => 'nullable|string|max:255',

            'gpa' => 'nullable|numeric|min:0|max:4',
            'average_score' => 'nullable|numeric|min:0|max:100',

            'is_active' => 'sometimes|boolean'
        ];
    }
}
