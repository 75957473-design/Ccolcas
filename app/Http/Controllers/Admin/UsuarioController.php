<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $rolId = $request->input('rol_id');

        $usuarios = Usuario::with('rol')
            ->when($q !== '', fn ($qb) => $qb->where(fn ($w) => $w->where('nombre_completo', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->when($rolId, fn ($qb) => $qb->where('rol_id', $rolId))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $roles = Rol::orderBy('nombre')->get();

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'q', 'rolId'));
    }

    public function create()
    {
        $roles = Rol::orderBy('nombre')->get();

        return view('admin.usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'rol_id' => ['required', 'exists:roles,id'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $data['activo'] = $data['activo'] ?? true;

        Usuario::create($data);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(Usuario $usuario)
    {
        $roles = Rol::orderBy('nombre')->get();

        return view('admin.usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        $data = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:usuarios,email,' . $usuario->id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'rol_id' => ['required', 'exists:roles,id'],
            'activo' => ['nullable', 'boolean'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $usuario->update($data);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggle(Usuario $usuario)
    {
        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('success', $usuario->activo ? 'Usuario activado.' : 'Usuario desactivado.');
    }
}
