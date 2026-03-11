<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\GrupoCliente;
use App\Models\Persona;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-cliente', ['only' => ['index']]);
        $this->middleware('permission:crear-cliente', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-cliente', ['only' => ['edit', 'update']]);
        $this->middleware('permission:update-estado-cliente', ['only' => ['updateEstado']]);
    }

    public function index()
    {
        $clientes  = Cliente::with(['persona.documento', 'grupoCliente'])->get();
        $grupos    = GrupoCliente::where('estado', true)->get();
        $documentos = Documento::all();
    
        return view('admin.cliente.index', compact('clientes', 'grupos', 'documentos'));
    }

    public function create()
    {
        $documentos = Documento::all();
        $grupos     = GrupoCliente::where('estado', true)->get();
        return view('admin.cliente.create', compact('documentos', 'grupos'));
    }

    public function store(StoreClienteRequest $request)
    {
        $data = $request->validated();

        $persona = Persona::create([
            'nombre_completo'  => $data['nombre_completo'],
            'direccion'        => $data['direccion'] ?? null,
            'telefono'         => $data['telefono'] ?? null,
            'tipo_persona'     => $data['tipo_persona'],
            'numero_documento' => $data['numero_documento'],
            'documento_id'     => $data['documento_id'],
        ]);

        Cliente::create([
            'persona_id'       => $persona->id,
            'grupo_cliente_id' => $data['grupo_cliente_id'] ?? null,
            'estado'           => true,
        ]);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Cliente $cliente)
    {
        $documentos = Documento::all();
        $grupos     = GrupoCliente::where('estado', true)->get();
        return view('admin.cliente.edit', compact('cliente', 'documentos', 'grupos'));
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        DB::beginTransaction();

        $data = $request->validated();

        $cliente->persona->update([
            'nombre_completo'  => $data['nombre_completo'],
            'direccion'        => $data['direccion']        ?? null,
            'telefono'         => $data['telefono']         ?? null,
            'tipo_persona'     => $data['tipo_persona'],
            'numero_documento' => $data['numero_documento'],
            'documento_id'     => $data['documento_id'],
        ]);

        $cliente->update([
            'grupo_cliente_id' => $data['grupo_cliente_id'],
        ]);

        DB::commit();
        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function updateEstado(Cliente $cliente)
    {
        $cliente->estado = !$cliente->estado;
        $cliente->save();

        return redirect()->route('clientes.index')
            ->with('success', $cliente->estado ? 'Cliente activado.' : 'Cliente desactivado.');
    }
}
