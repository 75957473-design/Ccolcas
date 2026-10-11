<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginFeatureTest extends TestCase
{
    // Cada prueba usa una base aislada con las migraciones reales del proyecto.
    use RefreshDatabase;

    private function crearUsuario(Rol $rol, string $correo = 'usuario@ccolcas.com'): Usuario
    {
        // Crear un usuario de prueba asociado al rol recibido.
        return Usuario::create([
            'nombre_completo' => 'Usuario de prueba',
            'rol_id' => $rol->id,
            'email' => $correo,
            'password' => 'clave-segura',
            'activo' => true,
        ]);
    }

    public function test_muestra_el_formulario_de_inicio_de_sesion(): void
    {
        // Abrir la página principal donde se encuentra el formulario de acceso.
        $respuesta = $this->get(route('home'));

        // Confirmar que se solicitan correo y contraseña y que el formulario apunta a la ruta de login.
        $respuesta->assertOk()
            ->assertSee('action="' . route('login') . '"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);
    }

    public function test_permite_iniciar_sesion_con_credenciales_validas(): void
    {
        // Registrar un usuario con rol administrativo y credenciales conocidas.
        $rol = Rol::create(['nombre' => 'Administrador']);
        $usuario = $this->crearUsuario($rol);

        // Enviar el formulario de acceso con correo y contraseña correctos.
        $respuesta = $this->from(route('home'))->post(route('login'), [
            'email' => 'usuario@ccolcas.com',
            'password' => 'clave-segura',
        ]);

        // El usuario debe quedar autenticado y ser dirigido al panel según su rol.
        $respuesta->assertRedirect('/admin/dashboard')
            ->assertSessionHas('success', 'Acceso al panel concedido.');
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_rechaza_credenciales_incorrectas_y_muestra_un_mensaje(): void
    {
        // Crear un usuario para comprobar el rechazo de una contraseña incorrecta.
        $rol = Rol::create(['nombre' => 'Administrador']);
        $this->crearUsuario($rol);

        // Intentar iniciar sesión con una contraseña que no corresponde al usuario.
        $respuesta = $this->from(route('home'))->post(route('login'), [
            'email' => 'usuario@ccolcas.com',
            'password' => 'incorrecta',
        ]);

        // El acceso se rechaza, se conserva el error para mostrarlo en el formulario y no hay sesión.
        $respuesta->assertRedirect(route('home'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->get(route('home'))
            ->assertSeeText('Las credenciales ingresadas no coinciden con nuestros registros.');
    }

    public function test_solicita_correo_y_contrasena_obligatorios(): void
    {
        // Enviar el formulario sin completar ninguno de los campos requeridos.
        $respuesta = $this->from(route('home'))->post(route('login'), []);

        // La validación debe señalar tanto el correo como la contraseña.
        $respuesta->assertRedirect(route('home'))
            ->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_identifica_el_rol_cliente_y_lo_dirige_a_la_portada(): void
    {
        // El ID del rol no importa: la aplicación identifica al cliente por el nombre del rol.
        $rol = Rol::create(['nombre' => 'Cliente']);
        $usuario = $this->crearUsuario($rol, 'cliente@ccolcas.com');

        // Iniciar sesión y comprobar la redirección propia del rol cliente.
        $respuesta = $this->post(route('login'), [
            'email' => 'cliente@ccolcas.com',
            'password' => 'clave-segura',
        ]);

        $respuesta->assertRedirect('/')
            ->assertSessionHas('success', '¡Bienvenido de nuevo!');
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_registro_crea_el_rol_cliente_si_la_tabla_roles_esta_vacia(): void
    {
        // La base de prueba inicia sin roles, igual que la base local que produjo la excepción.
        $this->assertDatabaseCount('roles', 0);

        $respuesta = $this->post(route('cliente.register'), [
            'nombre_completo' => 'Juan Pérez',
            'email' => 'juan.registro@ccolcas.com',
            'password' => 'clave123',
            'password_confirmation' => 'clave123',
            'tipo_documento' => 'DNI',
            'numero_documento' => '63215478',
            'telefono' => '987654321',
            'direccion_entrega' => 'Cusco',
        ]);

        // El registro debe crear el rol y asociar al usuario sin depender de que tenga ID 5.
        $respuesta->assertRedirect(route('home'))
            ->assertSessionHas('success', '¡Registro completado con éxito!');

        $this->assertDatabaseHas('roles', ['nombre' => 'Cliente']);
        $this->assertDatabaseHas('clientes', [
            'numero_documento' => '63215478',
            'email' => 'juan.registro@ccolcas.com',
        ]);
        $this->assertSame('Cliente', Usuario::where('email', 'juan.registro@ccolcas.com')->firstOrFail()->rol->nombre);
    }

    public function test_cierra_sesion_y_redirige_a_la_portada(): void
    {
        // Autenticar al usuario antes de solicitar el cierre de sesión.
        $rol = Rol::create(['nombre' => 'Administrador']);
        $usuario = $this->crearUsuario($rol);

        // Enviar la petición POST de logout.
        $respuesta = $this->actingAs($usuario)->post(route('logout'));

        // Confirmar que la sesión termina y se informa al usuario.
        $respuesta->assertRedirect('/')
            ->assertSessionHas('success', 'Sesión cerrada correctamente.');
        $this->assertGuest();
    }
}
