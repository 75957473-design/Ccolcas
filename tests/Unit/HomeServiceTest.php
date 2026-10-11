<?php

namespace Tests\Unit;

use App\Services\HomeService;
use PHPUnit\Framework\TestCase;

class HomeServiceTest extends TestCase
{
    public function test_muestra_informacion_de_la_empresa(): void
    {
        // Given: un servicio de la página principal
        $service = new HomeService();

        // When: se solicita la información de la empresa
        $info = $service->informacionEmpresa();

        // Then: debe contener los datos básicos de CCOLCAS
        $this->assertSame('CCOLCAS', $info['nombre']);
        $this->assertArrayHasKey('descripcion', $info);
        $this->assertArrayHasKey('mision', $info);
        $this->assertArrayHasKey('vision', $info);
    }

    public function test_muestra_opciones_de_navegacion(): void
    {
        // Given: un servicio de la página principal
        $service = new HomeService();

        // When: se solicitan las opciones de navegación
        $opciones = $service->opcionesNavegacion();

        // Then: debe haber al menos 3 opciones (Catálogo, Login, Registro)
        $this->assertCount(3, $opciones);
        $this->assertSame('Catálogo', $opciones[0]['nombre']);
        $this->assertSame('Iniciar sesión', $opciones[1]['nombre']);
        $this->assertSame('Registrarse', $opciones[2]['nombre']);
    }

    public function test_accede_al_catalogo(): void
    {
        // Given: un servicio con las opciones de navegación
        $service = new HomeService();

        // When: se busca la opción "Catálogo"
        $opcion = $service->buscarOpcion('Catálogo');

        // Then: debe encontrar la opción con su ruta correcta
        $this->assertNotNull($opcion);
        $this->assertSame('/catalogo', $opcion['ruta']);
    }

    public function test_accede_al_login(): void
    {
        // Given: un servicio con las opciones de navegación
        $service = new HomeService();

        // When: se busca la opción "Iniciar sesión"
        $opcion = $service->buscarOpcion('Iniciar sesión');

        // Then: debe encontrar la opción con su ruta correcta
        $this->assertNotNull($opcion);
        $this->assertSame('/login', $opcion['ruta']);
    }

    public function test_accede_al_registro(): void
    {
        // Given: un servicio con las opciones de navegación
        $service = new HomeService();

        // When: se busca la opción "Registrarse"
        $opcion = $service->buscarOpcion('Registrarse');

        // Then: debe encontrar la opción con su ruta correcta
        $this->assertNotNull($opcion);
        $this->assertSame('/registro', $opcion['ruta']);
    }

    public function test_buscar_opcion_inexistente_devuelve_null(): void
    {
        // Given: un servicio con las opciones de navegación
        $service = new HomeService();

        // When: se busca una opción que no existe
        $opcion = $service->buscarOpcion('Contacto');

        // Then: debe devolver null
        $this->assertNull($opcion);
    }
}