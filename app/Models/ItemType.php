<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    protected $table = 'item_types';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    // Later relation to items
    public function items()
    {
        return $this->hasMany('App\Models\Item', 'item_type_id', 'id');
    }
}
