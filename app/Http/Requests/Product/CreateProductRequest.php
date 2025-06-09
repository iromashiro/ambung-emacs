<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() &&
            auth()->user()->role === 'seller' &&
            auth()->user()->status === 'active';
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'stock' => 'required|integer|min:0|max:999999',
            'category_id' => 'required|exists:categories,id',
            'is_featured' => 'sometimes|boolean',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Product name is required',
            'price.min' => 'Price must be at least 0.01',
            'stock.min' => 'Stock cannot be negative',
            'category_id.exists' => 'Selected category does not exist',
            'images.*.image' => 'Each file must be an image',
            'images.*.max' => 'Each image must not exceed 2MB'
        ];
    }
}
