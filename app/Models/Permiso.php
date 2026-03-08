<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    use HasFactory;

    protected $table = 'permisos';
    protected $fillable = ['name','modulo'];

    public function roles()
    {
        return $this->belongsToMany(Rol::class,'roles_permisos','permission_id','role_id'
        );
    }
}
