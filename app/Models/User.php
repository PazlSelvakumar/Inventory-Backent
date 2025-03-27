<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens,HasRoles;
    
    protected $guard_name = 'web'; 
   
    protected $fillable = [
        'type_id', 
        'dept_id',
        'name',
        'email',
        'password',
        'role',
        'permissions'
    ];

    
    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function isAdmin() {
        return $this->role === 'admin';
    }

    public function isManager() {
        return $this->role === 'manager';
    }

     public function setPermissionsAttribute($value)
     {
         $this->attributes['permissions'] = is_array($value) 
             ? json_encode($value) 
             : $value;
     }
 
     public function getPermissionsAttribute($value)
     {
        return collect(json_decode($value, true)); 
    }

    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array'
        ];
    }
}
