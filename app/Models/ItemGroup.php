<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemGroup extends Model
{
    protected $table = 'item_groups';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    // Keep relation for later (won't break if table missing if we don't withCount yet)
    public function subGroups()
    {
        // Only define if class exists (safe)
        if (class_exists(ItemSubGroup::class)) {
            return $this->hasMany(ItemSubGroup::class, 'item_group_id', 'id');
        }

        // fallback dummy relation to avoid crash in some cases
        return $this->hasMany(self::class, 'id', 'id')->whereRaw('1=0');
    }
}
