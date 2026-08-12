<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Represents actions/buttons within a page
class RoleOption extends Model
{
    protected $table = 'role_options';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['option_name', 'option_code', 'page_id', 'status'];

    // Option BELONGS TO one Page
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id', 'page_id');
    }
}