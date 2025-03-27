<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $table = 'departments';
    
    protected $fillable = [
        'department_name',
        'type_id',
    ]; 

    public function type()
    { 
        return $this->belongsTo(TypeMaster::class, 'type_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'department_id');
    }


}
