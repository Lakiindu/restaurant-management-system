<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $table = 'item_categories';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function subCategories()
    {
        return $this->hasMany(ItemSubCategory::class, 'item_category_id', 'id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\Item', 'item_category_id', 'id');
    }
}
