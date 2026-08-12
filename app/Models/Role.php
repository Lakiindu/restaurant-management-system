<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// ✅ Check if user can use specific OPTION (button/action)
class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role_id';
    public $timestamps = true;

    protected $fillable = ['role_name', 'description', 'status'];

    // Role HAS MANY users
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    // Role HAS MANY page permissions
    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id', 'role_id');
    }

    // Role HAS MANY option permissions (buttons/actions)
    public function optionPermissions()
    {
        return $this->hasMany(RoleOptionPermission::class, 'roles_id', 'role_id');
    }
}