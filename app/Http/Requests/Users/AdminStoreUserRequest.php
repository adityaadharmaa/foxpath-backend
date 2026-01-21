<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class AdminStoreUserRequest extends FormRequest
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
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email:dns|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,users'
        ];
    }

     public function messages(): array
    {
        return [
            'username.required' => 'Username is required',
            'username.unique'   => 'Username already exists',
            'email.required'    => 'Email is required',
            'email.email'       => 'Email is not valid',
            'email.unique'      => 'Email already exists',
            'password.required' => 'Password is required',
            'password.min'      => 'Password must be at least 8 characters',
            'password.confirmed'=> 'Password confirmation does not match',
            'role.required'     => 'Role is required',
            'role.in'           => 'Role must be either admin or user',
        ];
    }
}
