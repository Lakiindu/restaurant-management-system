<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    protected $table = 'menu_categories';
    protected $guarded = [];

    public function menuItems()
    {
        return $this->hasMany('App\Models\MenuItem', 'category_id', 'id');
    }
}
