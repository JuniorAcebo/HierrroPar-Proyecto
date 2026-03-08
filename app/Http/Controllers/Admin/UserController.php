<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Almacen;
use App\Models\Rol;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-user', ['only' => ['index']]);
        $this->middleware('permission:crear-user', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:update-estado-user', ['only' => ['updateEstado']]);
    }
  
    public function index()
    {
        $users = User::all();
        return view('admin.user.index', compact('users'));
    }

    public function create()
    {
        $roles = Rol::where('estado', true)->get();
        $almacenes = Almacen::where('estado', true)->get();
        return view('admin.user.create', compact('roles', 'almacenes'));
    }

    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $userData = $request->validated();
            $userData['password'] = Hash::make($userData['password']);

            // Resolver role_id y manejar almacen_id para ADMINISTRADOR
            $rol = Rol::where('name', $request->role)->first();
            $userData['role_id'] = $rol->id;

            User::create($userData);

            DB::commit();
            return redirect()->route('users.index')
                ->with('success', 'Usuario registrado correctamente');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear usuario: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Error al registrar usuario');
        }
    }

    public function edit(User $user)
    {
        $roles = Rol::all();
        $almacenes = Almacen::where('estado', true)->get();
        return view('admin.user.edit', compact('user', 'roles', 'almacenes'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            $userData = $request->validated();
            
            //Manejar password
            if (!empty($userData['password'])) {
                $userData['password'] = Hash::make($userData['password']);
            } else {
                unset($userData['password']);
            }

            // Resolver role_id y manejar almacen_id para ADMINISTRADOR
            $rol = Rol::where('name', $request->role)->first();
            $userData['role_id'] = $rol->id;

            if ($request->role === 'ADMINISTRADOR') {
                $userData['almacen_id'] = null;
            }

            $user->update($userData);

            // Si usas Spatie Roles, esto sincroniza los roles por nombre
            if (method_exists($user, 'syncRoles')) {
                $user->syncRoles([$request->role]);
            }

            DB::commit();
            return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar usuario: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    public function updateEstado(User $user)
    {
        $user->estado = $user->estado === 'activo' ? 'inactivo' : 'activo';
        $user->save();

        return redirect()->back()->with('success', 'Estado del usuario actualizado correctamente');
    }
}