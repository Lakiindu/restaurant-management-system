<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'menu_items';
    protected $primaryKey = 'item_id';
    public $timestamps = true;

    protected $fillable = [
        'item_name',
        'item_code',
        'category_id',
        'price',
        'description',
        'image',
        'is_vegetarian',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id', 'category_id');
    }
}
