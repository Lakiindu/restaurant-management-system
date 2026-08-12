<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

//Groups pages into categories
class PageCategory extends Model
{
    protected $table = 'page_categories';
    protected $primaryKey = 'category_id';
    public $timestamps = true;

    protected $fillable = ['category_name', 'description', 'status'];

    // Category HAS MANY pages
    public function pages()
    {
        return $this->hasMany(Page::class, 'category_id', 'category_id');
    }
}