<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $fillable = ['supplier_name','product_id'];

    public function products()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function supplier_branches()
    {
        return $this->hasMany(supplierBranch::class,'supplier_id');
    }
}
