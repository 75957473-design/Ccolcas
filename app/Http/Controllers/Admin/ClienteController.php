<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $clientes = Cliente::with(['usuario', 'pedidos'])
            ->when($q !== '', fn ($qb) => $qb->where(function ($w) use ($q) {
                $w->where('nombre_razon_social', 'like', "%{$q}%")
                  ->orWhere('numero_documento', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('telefono', 'like', "%{$q}%");
            }))
            ->withCount('pedidos')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.clientes.index', compact('clientes', 'q'));
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['usuario', 'pedidos.estado', 'pedidos.detalles']);

        return view('admin.clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('admin.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre_razon_social' => ['required', 'string', 'max:150'],
            'tipo_documento' => ['required', 'in:DNI,RUC'],
            'numero_documento' => ['required', 'string', 'max:15', 'unique:clientes,numero_documento,' . $cliente->id],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion_entrega' => ['nullable', 'string', 'max:500'],
            'persona_recepcion' => ['nullable', 'string', 'max:100'],
        ]);

        $cliente->update($data);

        return redirect()->route('admin.clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }
}
