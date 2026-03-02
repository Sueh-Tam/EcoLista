<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_price_per_kg', 'brand_id'];

    protected $casts = [
        'is_price_per_kg' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function listItems()
    {
        return $this->hasMany(ListItem::class);
    }
}
