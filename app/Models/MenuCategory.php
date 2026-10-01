<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    protected $table = 'menu_categories';
    protected $primaryKey = 'category_id';
    public $timestamps = true;

    protected $fillable = ['category_name', 'description', 'image', 'status'];

    public function items()
    {
        return $this->hasMany(MenuItem::class, 'category_id', 'category_id');
    }
}
