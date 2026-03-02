<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingList extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'creation_date', 'total_value', 'notes', 'market_name', 'is_completed'];

    protected $casts = [
        'creation_date' => 'date',
        'total_value' => 'decimal:2',
        'is_completed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listItems()
    {
        return $this->hasMany(ListItem::class, 'list_id');
    }
}
