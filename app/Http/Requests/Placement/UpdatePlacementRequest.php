<?php

namespace App\Http\Requests\Placement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePlacementRequest extends FormRequest
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
            'placement_start_at' => ['required', 'date', 'date_format:Y-m-d'],
            'placement_end_at'   => ['nullable', 'date', 'after:placement_start_at'],
            'duration_months'    => ['nullable', 'integer', 'min:1'],
        ];
    }
}
