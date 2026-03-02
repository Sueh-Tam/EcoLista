<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingList extends Model
{
    protected $fillable = ['user_id', 'creation_date', 'total_value', 'notes'];

    protected $casts = [
        'creation_date' => 'date',
        'total_value' => 'decimal:2',
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
