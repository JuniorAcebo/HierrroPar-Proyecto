<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRolRequest;
use App\Http\Requests\UpdateRolRequest;
use App\Models\Permiso;
use App\Models\Rol;

class RolController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-role', ['only' => ['index']]);
        $this->middleware('permission:crear-role', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-role', ['only' => ['edit', 'update']]);
        $this->middleware('permission:update-estado-role', ['only' => ['updateEstado']]);
    }

    public function index()
    {
        $roles = Rol::all();
        return view('admin.rol.index', compact('roles'));
    }

    public function create()
    {
        $permisos = Permiso::all();
        return view('admin.rol.create', compact('permisos'));
    }

    public function store(StoreRolRequest $request)
    {
        $data = $request->validated();

        $rol = Rol::create([
            'name' => $data['name'],
            'estado' => 1
        ]);

        $rol->permisos()->sync($request->input('permisos', []));

        return redirect()->route('roles.index')
            ->with('success','Rol creado correctamente');
    }

    public function edit(Rol $role)
    {
        $permisos = Permiso::all();
        $rolePermisoIds = $role->permisos()->pluck('permisos.id')->all();

        return view('admin.rol.edit', compact('role', 'permisos', 'rolePermisoIds'));
    }

    public function update(UpdateRolRequest $request, Rol $role)
    {
        $data = $request->validated();

        $role->update([
            'name' => $data['name'],
        ]);

        $role->permisos()->sync($data['permisos']);

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado correctamente');
    }

    public function updateEstado(Rol $role)
    {
        $role->estado = $role->estado === 1 ? 0 : 1;
        $role->save();

        return redirect()->back()->with('success', 'Estado del rol actualizado correctamente');
    }
}
