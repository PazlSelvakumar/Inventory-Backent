<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'category_id', 
        'product_name', 
        'product_price',
        'product_description', 
        'product_code',
        'hsn_code',
        'cgst',
        'sgst',
        'igst',
        'total'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function supplierbranch()
    {           
        return $this->hasMany(Supplier::class);
    }
}
