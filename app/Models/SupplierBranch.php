<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierBranch extends Model
{
    protected $table = 'supplier_branches';
    public $fillable = [
        'supplier_id',
        'branch_name',
        'mail_id', 
        'mobile_number', 
        'phone_number',
        'branch_address',
        'tin_number',
        'gst_number'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id');
    }
    
}
