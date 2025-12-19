<?php

namespace App\Http\Requests\Programs;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'registration_starts_at' => ['sometimes', 'nullable', 'date'],
            'registration_ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:registration_starts_at'],
            'cohort_starts_at' => ['sometimes', 'nullable', 'date'],
            'placement_duration_months' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:24'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_ends_at.after_or_equal' => 'The registration end date must be a date after or equal to the registration start date.',
        ];
    }
}
