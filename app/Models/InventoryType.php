<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryType extends Model
{
    protected $table = 'inventory_types';
    protected $primaryKey = 'type_id';
    public $timestamps = true;

    protected $fillable = [
        'type_name',
        'description',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'type_id', 'type_id');
    }
}
