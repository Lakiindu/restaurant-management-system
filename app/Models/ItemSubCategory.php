<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSubCategory extends Model
{
    protected $table = 'item_sub_categories';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'item_category_id',
        'name',
        'description',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id', 'id');
    }
}
