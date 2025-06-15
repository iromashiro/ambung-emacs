<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorization handled in controller
    }

    public function rules()
    {
        return [
            'status' => [
                'required',
                'string',
                'in:new,accepted,dispatched,delivered,canceled'
            ]
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status selected'
        ];
    }
}
