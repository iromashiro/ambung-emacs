<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'seller';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:stores,name',
            'description' => 'required|string|max:1000',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Store name is required.',
            'name.unique' => 'Store name already exists.',
            'description.required' => 'Store description is required.',
            'address.required' => 'Store address is required.',
            'phone.required' => 'Store phone number is required.',
            'logo.image' => 'Logo must be an image file.',
            'logo.max' => 'Logo size cannot exceed 2MB.',
        ];
    }
}
