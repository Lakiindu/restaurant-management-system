<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Represents a system page
class Page extends Model
{
    protected $table = 'pages';
    protected $primaryKey = 'page_id';
    public $timestamps = true;

    protected $fillable = [
        'page_name',
        'page_code',
        'route_name',
        'description',
        'category_id',
        'status',
    ];

    // Page BELONGS TO one Category
    public function category()
    {
        return $this->belongsTo(PageCategory::class, 'category_id', 'category_id');
    }

    // Page HAS MANY options (CRUD)
    public function roleOptions()
    {
        return $this->hasMany(RoleOption::class, 'page_id', 'page_id');
    }
}