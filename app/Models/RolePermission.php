<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Controls which ROLES can ACCESS which PAGES
class RolePermission extends Model
{
    protected $table = 'role_permissions';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['role_id', 'page_code', 'allow'];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_code', 'page_code');
    }
}