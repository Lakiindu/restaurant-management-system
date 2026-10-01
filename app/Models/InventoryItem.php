<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $table = 'inventory_items';
    protected $primaryKey = 'item_id';
    public $timestamps = true;

    protected $fillable = [
        'item_name',
        'item_code',
        'price',
        'type_id',
        'unit',
        'current_stock',
        'minimum_stock',
        'description',
        'status',
    ];

    public function type()
    {
        return $this->belongsTo(InventoryType::class, 'type_id', 'type_id');
    }

    // helper: is stock low?
    public function getIsLowStockAttribute()
    {
        return $this->current_stock <= $this->minimum_stock;
    }
}
