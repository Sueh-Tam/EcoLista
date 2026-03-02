<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required|string|max:200',
            'product_id' => 'nullable|integer|exists:products,id',
            'brand_name' => 'required|string|max:255',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'quantity' => 'required|numeric|min:0.001',
            'unit_price' => 'nullable|numeric|min:0',
            'promo_buy_quantity' => 'nullable|integer|min:2',
            'promo_price' => 'nullable|numeric|min:0',
            'is_price_per_kg' => 'boolean',
        ];
    }
}
