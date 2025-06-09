<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class CreateOrderRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role === 'buyer';
    }

    public function rules()
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:999',
            'shipping_address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:255'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->items as $index => $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    $validator->errors()->add("items.{$index}.product_id", "Product not found");
                    continue;
                }

                if ($product->status !== 'active') {
                    $validator->errors()->add("items.{$index}.product_id", "Product is not available");
                }

                if ($product->stock < $item['quantity']) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "Insufficient stock for {$product->name}. Available: {$product->stock}"
                    );
                }
            }
        });
    }
}
