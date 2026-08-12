<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Controls which ROLES can use which OPTIONS/ACTIONS
class RoleOptionPermission extends Model
{
    protected $table = 'role_option_permissions';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['roles_id', 'option_code', 'allow'];

    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id', 'role_id');
    }

    public function roleOption()
    {
        return $this->belongsTo(RoleOption::class, 'option_code', 'option_code');
    }
}