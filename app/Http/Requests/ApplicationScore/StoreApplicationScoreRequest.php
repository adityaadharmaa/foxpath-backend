<?php

namespace App\Http\Requests\ApplicationScore;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationScoreRequest extends FormRequest
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
            'scores' => ['required', 'array', 'min:1'],
            'scores.*.criteria_id' => ['required', 'exists:criterias,id'],
            'scores.*.value' => ['required', 'numeric', 'min:0'],
        ];
    }
}
