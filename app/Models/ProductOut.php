<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOut extends Model
{
    protected $fillable = [
        'customer_name',
        'date',
        'invoice',
        'total',
        'money_received',
        'discount',
        'return',
        'payment_method',
        'remark',
        'casher',
        'user_id',
        'session_cashier_id',
        'isDeleted'
    ];

    protected $casts = [
        'isDeleted' => 'boolean',
        'date' => 'date'
    ];

    public function details()
    {
        return $this->hasMany(ProductOutDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashierSession()
    {
        return $this->belongsTo(CashierSession::class, 'session_cashier_id');
    }
}
