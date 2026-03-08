<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;
    
    protected $table = 'roles';
    protected $fillable = ['name','estado'];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permisos()
    {
        return $this->belongsToMany( Permiso::class,'roles_permisos','role_id','permission_id'
        );
    }
}
