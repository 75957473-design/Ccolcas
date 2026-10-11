<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('welcome');
    }

    public function login(Request $request)
    {
        // 1. Validar inputs
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Buscar al usuario por correo
        $user = Usuario::where('email', $request->email)->first();

        // 3. Verificar contraseña con hash seguro
        if ($user && Hash::check($request->password, $user->password)) {

            // Verificar si el usuario está activo
            if (!$user->activo) {
                return back()->withErrors([
                    'email' => 'Su cuenta se encuentra desactivada.',
                ])->onlyInput('email');
            }

            // Iniciar sesión manualmente
            Auth::login($user);
            $request->session()->regenerate();

            // Redirección según Rol
            if ($user->esCliente() || $user->cliente) {
                return redirect('/')->with('success', '¡Bienvenido de nuevo!');
            }

            return redirect('/admin/dashboard')->with('success', 'Acceso al panel concedido.');
        }

        // 4. Si la contraseña o correo no coinciden
        return back()->withErrors([
            'email' => 'Las credenciales ingresadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Sesión cerrada correctamente.');
    }
}