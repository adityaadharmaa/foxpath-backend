<?php

namespace App\Http\Requests\InternshipApplications;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternshipApplicationRequest extends FormRequest
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
            'programs_id' => ['required', 'exists:programs,id']
        ];
    }
}
