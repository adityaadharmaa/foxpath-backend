<?php

namespace App\Http\Requests\ApplicationDocument;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationDocumentRequest extends FormRequest
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
            // 'internship_applications_id' => ['required', 'exists:internship_applications,id'],
            'type' => ['required', 'in:cv,transcript,portofolio,certificate,other'],
            'file' => ['required', 'file', 'mimetypes:application/pdf,image/*', 'max:5120']
        ];
    }
}
