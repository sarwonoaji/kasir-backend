<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'barcode',
        'name',
        'price',
        'stock',
        'unit',
        'description',
        'is_active',
        'isDeleted',
    ];
}
