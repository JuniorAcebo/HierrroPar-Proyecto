<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlmacenRequest;
use App\Http\Requests\UpdateAlmacenRequest;
use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use App\Models\Producto;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlmacenController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-almacen', ['only' => ['index']]);
        $this->middleware('permission:crear-almacen', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-almacen', ['only' => ['edit', 'update']]);
        $this->middleware('permission:update-estado-almacen', ['only' => ['updateEstado']]);
    }

    public function index()
    {
        $almacenes = Almacen::all();
        return view('admin.almacen.index', compact('almacenes'));
    }

    public function create()
    {
        return view('admin.almacen.create');
    }


    //aqui es donde va la informacion para crear un nuevo almacen
    public function store(StoreAlmacenRequest $request)
    {
        DB::beginTransaction();

        try {
            $almacen = Almacen::create($request->validated());
            $productos = Producto::where('estado', 1)->get();
            
            foreach ($productos as $producto) {
                InventarioAlmacen::create([
                    'almacen_id' => $almacen->id,
                    'producto_id' => $producto->id,
                    'stock' => 0,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('almacenes.index')
                ->with('success', 'Almacén registrado correctamente con todos los productos integrados');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al crear almacén: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al crear el almacén: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Almacen $almacen)
    {
        return view('admin.almacen.edit', compact('almacen'));
    }

    public function update(UpdateAlmacenRequest $request, Almacen $almacen)
    {
        try {
            DB::beginTransaction();

            $almacen->update($request->validated());

            DB::commit();
            return redirect()->route('almacenes.index')
                ->with('success', 'Almacén actualizado correctamente');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar almacén: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar almacén');
        }
    }

    public function updateEstado(Almacen $almacen)
    {
        $almacen->estado = !$almacen->estado;
        $almacen->save();

        return redirect()->back()->with('success', 'Estado del almacén actualizado correctamente');
    }
}
