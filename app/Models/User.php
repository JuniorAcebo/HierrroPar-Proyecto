<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'estado',
        'almacen_id',
        'role_id'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'user_id');
    }

    public function traslados()
    {
        return $this->hasMany(Traslado::class, 'user_id');
    }

    public function ajustesStock()
    {
        return $this->hasMany(AjusteStock::class, 'user_id');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'user_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }   

    public function role()
    {
        return $this->belongsTo(Rol::class, 'role_id');
    }

    public function hasPermission($permiso)
    {
        if (!$this->role) {
            return false;
        }

        return $this->role
            ->permisos()
            ->where('name', $permiso)
            ->exists();
    }
}
