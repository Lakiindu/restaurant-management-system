<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'menu_items';

    protected $fillable = [
        'item_code',
        'name',
        'category_id',
        'price',
        'description',
        'image',
        'is_vegetarian',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_vegetarian' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function ingredients()
    {
        return $this->hasMany(MenuItemIngredient::class, 'menu_item_id');
    }

    public function inventoryItems()
    {
        return $this->belongsToMany(Item::class, 'menu_item_ingredients', 'menu_item_id', 'inventory_item_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        // default fallback
        return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80';
    }
}
