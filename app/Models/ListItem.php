<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListItem extends Model
{
    protected $fillable = [
        'list_id', 'product_id', 'quantity', 'unit_price',
        'promo_buy_quantity', 'promo_pay_quantity', 'promo_price', 'item_total'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'promo_price' => 'decimal:2',
        'item_total' => 'decimal:2',
    ];

    public function shoppingList()
    {
        return $this->belongsTo(ShoppingList::class, 'list_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
