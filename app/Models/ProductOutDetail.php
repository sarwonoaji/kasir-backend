<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOutDetail extends Model
{
    protected $fillable = [
        'product_out_id',
        'product_id',
        'quantity',
        'price',
        'total_price',
        'isDeleted'
    ];

    protected $casts = [
        'isDeleted' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productOut()
    {
        return $this->belongsTo(ProductOut::class);
    }
}
