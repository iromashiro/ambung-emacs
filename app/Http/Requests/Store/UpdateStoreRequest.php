<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Store;

class UpdateStoreRequest extends FormRequest
{
    public function authorize()
    {
        $store = Store::findOrFail($this->route('store'));
        return auth()->check() && auth()->user()->id === $store->seller_id;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Store name is required',
            'description.required' => 'Store description is required',
            'address.required' => 'Store address is required',
            'phone.required' => 'Contact phone is required',
            'logo.image' => 'Logo must be an image',
            'logo.max' => 'Logo must not exceed 2MB'
        ];
    }
}
