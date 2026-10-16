<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItemIngredient extends Model
{
    use HasFactory;

    protected $table = 'menu_item_ingredients';

    protected $fillable = [
        'menu_item_id',
        'inventory_item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(Item::class, 'inventory_item_id');
    }
}
