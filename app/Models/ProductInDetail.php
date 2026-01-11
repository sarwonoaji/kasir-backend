<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInDetail extends Model
{
    protected $fillable = [
        'id',
        'product_in_id',
        'product_id',
        'quantity',
        'price',
        'total_price',
    ];

    public function productIn()
    {
        return $this->belongsTo(ProductIn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}