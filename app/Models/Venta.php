<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'fecha_hora',
        'numero_comprobante',
        'total',
        'estado_comprobante',
        'estado',
        'almacen_id',
        'cliente_id',
        'user_id',
        'nota_personal',
        'nota_cliente'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }


    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    /**
     * Propiedad virtual para saber si la venta está en estado terminal.
     */
    public function getIsLockedAttribute()
    {
        return in_array($this->estado, ['completada', 'cancelada']);
    }

    /**
     * Scope para filtrar ventas según el request.
     */
    public function scopeFilter($query, $request)
    {
        $busqueda = $request->get('busqueda');
        $sort     = $request->get('sort', 'fecha_hora');
        $direction = $request->get('direction', 'desc');

        $query->with(['cliente.persona', 'user.almacen', 'detalles.producto', 'almacen']);

        if ($request->has('ids')) {
            $query->whereIn('ventas.id', $request->input('ids'));
        }

        // Restricción por almacén del usuario (Administrador no tiene)
        if (auth()->user()->almacen_id) {
            $query->where('ventas.almacen_id', auth()->user()->almacen_id);
        } elseif ($request->filled('almacen_id')) {
            $query->where('ventas.almacen_id', $request->input('almacen_id'));
        }

        if ($request->filled('producto_id')) {
            $query->whereHas('detalles', function ($q) use ($request) {
                $q->where('producto_id', $request->input('producto_id'));
            });
        }

        if ($request->filled('tipo_comprobante')) {
            $query->where('ventas.estado_comprobante', $request->input('tipo_comprobante'));
        }

        if ($request->filled('estado')) {
            $query->where('ventas.estado', $request->input('estado'));
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('ventas.fecha_hora', '>=', $request->input('fecha_inicio'));
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('ventas.fecha_hora', '<=', $request->input('fecha_fin'));
        }

        if ($request->filled('monto_min')) {
            $query->where('ventas.total', '>=', $request->input('monto_min'));
        }
        if ($request->filled('monto_max')) {
            $query->where('ventas.total', '<=', $request->input('monto_max'));
        }

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('ventas.numero_comprobante', 'like', "%{$busqueda}%")
                    ->orWhereHas('cliente.persona', function ($pq) use ($busqueda) {
                        $pq->where('nombre_completo', 'like', "%{$busqueda}%")
                            ->orWhere('numero_documento', 'like', "%{$busqueda}%");
                    })
                    ->orWhere('ventas.estado_comprobante', 'like', "%{$busqueda}%");
            });
        }

        switch ($sort) {
            case 'cliente':
                $query->join('clientes', 'ventas.cliente_id', '=', 'clientes.id')
                    ->join('personas', 'clientes.persona_id', '=', 'personas.id')
                    ->select('ventas.*')
                    ->orderBy('personas.razon_social', $direction);
                break;
            case 'numero_comprobante':
            case 'total':
            case 'fecha_hora':
            case 'estado':
                $query->orderBy('ventas.'.$sort, $direction);
                break;
            default:
                $query->latest('ventas.fecha_hora');
                break;
        }

        return $query;
    }
}