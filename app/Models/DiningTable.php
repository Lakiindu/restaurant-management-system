<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiningTable extends Model
{
    use HasFactory;
    protected $fillable = ['table_number', 'capacity', 'status'];

    public function activeOrder()
    {
        return $this->hasOne(Order::class, 'table_id')->whereNotIn('status', ['Completed', 'Cancelled']);
    }
}
