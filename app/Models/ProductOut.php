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
        'remark',
        'casher',
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
}
