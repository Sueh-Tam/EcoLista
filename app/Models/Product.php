<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'description'];

    public function listItems()
    {
        return $this->hasMany(ListItem::class);
    }
}
