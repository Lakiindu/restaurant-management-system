<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_no',
        'table_id',
        'waiter_id',
        'order_type',
        'status',
        'sub_total',
        'vat_amount',
        'discount_amount',
        'grand_total',
        'notes'
    ];

    public function table()
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }
    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id', 'user_id');
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
    public function payments()
    {
        return $this->hasMany(OrderPayment::class, 'order_id');
    }
}
