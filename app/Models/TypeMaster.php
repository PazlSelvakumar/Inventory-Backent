<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeMaster extends Model
{
    protected $fillable = [
        'type_name',
    ];

    public function department()
    {
        return $this->hasMany(Department::class,'type_id');
    }
}
