<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = true;

    // FILLABLE = Fields allowed for mass assignment
    protected $fillable = [
        'user_name',
        'email',
        'password',
        'must_change_password',
        'remember_token',
        'role_id',
        'employee_id',
        'branch_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // User BELONGS TO one Role
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    // Check if user can ACCESS a page
public function hasPagePermission(string $pageCode): bool
{
    return RolePermission::where('role_id', $this->role_id)
        ->where('page_code', $pageCode)
        ->where('allow', 1)
        ->exists();
}

// Check if user can use specific OPTION (button/action)
public function hasOptionPermission(string $optionCode): bool
{
    return RoleOptionPermission::where('roles_id', $this->role_id)
        ->where('option_code', $optionCode)
        ->where('allow', 1)
        ->exists();
}
}