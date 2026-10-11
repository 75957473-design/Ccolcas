<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterClienteController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validaciones de los campos del cliente
        $request->validate([
            'nombre_completo'    => 'required|string|max:255',
            'email'              => 'required|string|email|max:255|unique:usuarios,email',
            'password'           => 'required|string|min:6|confirmed',
            'tipo_documento'     => 'required|string',
            'numero_documento'   => 'required|string|max:20|unique:clientes,numero_documento',
            'telefono'           => 'nullable|string|max:20',
            'direccion_entrega'  => 'nullable|string|max:255',
        ]);

        // 2. Transacción para asegurar la creación del Usuario y su Perfil de Cliente
        DB::transaction(function () use ($request) {
            // Resolver el rol por nombre para no depender de un ID fijo en la tabla roles.
            $rolCliente = Rol::firstOrCreate(
                ['nombre' => 'Cliente'],
                ['descripcion' => 'Usuario registrado para realizar pedidos.']
            );

            // Crear el usuario con el ID real del rol Cliente existente o recién creado.
            $usuario = Usuario::create([
                'nombre_completo' => $request->nombre_completo,
                'email'           => $request->email,
                'password'        => $request->password, // <-- Sin Hash::make
                'rol_id'          => $rolCliente->id,
                'activo'          => true,
            ]);

            // Crear el perfil de Cliente enlazado al usuario creado
            Cliente::create([
                'usuario_id'          => $usuario->id,
                'tipo_documento'      => $request->tipo_documento,
                'numero_documento'    => $request->numero_documento,
                'nombre_razon_social' => $request->nombre_completo,
                'telefono'            => $request->telefono,
                'email'               => $request->email,
                'direccion_entrega'   => $request->direccion_entrega,
                'persona_recepcion'   => $request->nombre_completo,
            ]);

            // Iniciar sesión automáticamente
            Auth::login($usuario);
        });

        return redirect()->route('home')->with('success', '¡Registro completado con éxito!');
    }
}