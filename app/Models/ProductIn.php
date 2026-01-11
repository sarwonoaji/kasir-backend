<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductIn extends Model
{
    protected $fillable = [
        'id',
        'date',
        'no_transaksi',
        'remark',
    ];

    public function details()
    {
        return $this->hasMany(ProductInDetail::class);
    }
}