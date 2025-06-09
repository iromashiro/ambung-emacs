<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'is_seller' => ['boolean'],
            // UMKM fields
            'store_name' => ['required_if:is_seller,true', 'string', 'max:255'],
            'store_address' => ['required_if:is_seller,true', 'string'],
            'store_phone' => ['required_if:is_seller,true', 'string', 'max:20'],
            'store_description' => ['nullable', 'string'],
        ];
    }
}