<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'item_type_id',
        'stock_type',
        'item_category_id',
        'item_sub_category_id',
        'item_group_id',
        'item_sub_group_id',
        'item_code',
        'barcode',
        'unit_measure',
        'unit_size',
        'warranty',
        'cost_price',
        'sales_price',
        'discount_type',
        'reorder_level',
        'max_sales_qty',
        'client_description',
        'office_description',
        'status',
        'current_stock'
    ];

    public function type()
    {
        return $this->belongsTo(ItemType::class, 'item_type_id');
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(ItemSubCategory::class, 'item_sub_category_id');
    }

    public function group()
    {
        return $this->belongsTo(ItemGroup::class, 'item_group_id');
    }

    public function subGroup()
    {
        return $this->belongsTo(ItemSubGroup::class, 'item_sub_group_id');
    }
}
