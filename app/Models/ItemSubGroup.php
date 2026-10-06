<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSubGroup extends Model
{
    protected $table = 'item_sub_groups';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'item_group_id',
        'name',
        'description',
        'status',
    ];

    public function group()
    {
        return $this->belongsTo(ItemGroup::class, 'item_group_id', 'id');
    }
}
