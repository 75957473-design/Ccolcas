<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('productos')->orderBy('nombre')->paginate(12);

        return view('admin.categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categorias,nombre'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:categorias,slug'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['nombre']);
        $data['activo'] = $data['activo'] ?? true;

        Categoria::create($data);

        return back()->with('success', 'Categoría creada correctamente.');
    }

    public function update(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categorias,nombre,' . $categoria->id],
            'slug' => ['nullable', 'string', 'max:100', 'unique:categorias,slug,' . $categoria->id],
            'activo' => ['nullable', 'boolean'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['nombre']);
        }

        $categoria->update($data);

        return back()->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->exists()) {
            return back()->with('error', 'No se puede eliminar: tiene productos asociados. Desactívala en su lugar.');
        }

        $categoria->delete();

        return back()->with('success', 'Categoría eliminada.');
    }

    public function toggle(Categoria $categoria)
    {
        $categoria->update(['activo' => ! $categoria->activo]);

        return back()->with('success', $categoria->activo ? 'Categoría activada.' : 'Categoría desactivada.');
    }
}
