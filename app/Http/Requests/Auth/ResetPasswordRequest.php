<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
            'email' => ['required', 'email', 'exists:users,email'], // Pastikan email ada di tabel users
            'token' => ['required'],
            'password' => [
                'required',
                'confirmed', // Harus ada field password_confirmation di body
                Password::min(8)->letters()->numbers() // Minimal 8 char, ada huruf & angka
            ],
        ];
    }
}
