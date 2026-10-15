<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function items()
    {
        return $this->hasMany(GrnItem::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
