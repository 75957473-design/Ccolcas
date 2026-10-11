<?php

namespace Tests\Feature;

use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\StockPorAlmacen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Pruebas de integración HTTP: ejercitan rutas, consultas Eloquent y la vista del catálogo.
class CatalogoFeatureTest extends TestCase
{
    // Ejecuta las migraciones y revierte los datos al terminar cada prueba.
    use RefreshDatabase;

    private function crearCategoria(string $nombre, string $slug): Categoria
    {
        // Registrar una categoría activa que pueda asociarse a los productos del escenario.
        return Categoria::create([
            'nombre' => $nombre,
            'slug' => $slug,
            'activo' => true,
        ]);
    }

    private function crearProducto(Categoria $categoria, array $atributos = []): Producto
    {
        // Los valores predeterminados reducen el ruido; $atributos permite personalizar cada caso.
        // El código aleatorio evita colisiones con la restricción UNIQUE de la tabla.
        return Producto::create(array_merge([
            'codigo' => 'PROD-' . strtoupper(bin2hex(random_bytes(4))),
            'nombre' => 'Detergente Floral',
            'descripcion' => 'Detergente para limpieza de superficies.',
            'categoria_id' => $categoria->id,
            'unidad_medida_id' => null,
            'precio_venta' => 15.50,
            'stock_minimo' => 3,
            'activo' => true,
        ], $atributos));
    }

    private function asignarStock(Producto $producto, int $cantidad): void
    {
        // El inventario se almacena por producto y almacén, no directamente en la tabla productos.
        $almacen = Almacen::create([
            'nombre' => 'Almacén principal',
            'direccion' => 'Cusco',
            'activo' => true,
        ]);

        StockPorAlmacen::create([
            'producto_id' => $producto->id,
            'almacen_id' => $almacen->id,
            'cantidad' => $cantidad,
        ]);
    }

    public function test_cliente_puede_acceder_al_catalogo_y_solo_ve_productos_activos(): void
    {
        // Crear dos registros similares para aislar el criterio de disponibilidad comercial.
        $categoria = $this->crearCategoria('Detergentes', 'detergentes');
        $this->crearProducto($categoria, ['nombre' => 'Detergente disponible']);
        $this->crearProducto($categoria, [
            'nombre' => 'Detergente descontinuado',
            'activo' => false,
        ]);

        $respuesta = $this->get(route('home'));

        // GET / debe responder correctamente, mostrar el activo y excluir el inactivo.
        $respuesta->assertOk()
            ->assertSeeText('Detergente disponible')
            ->assertDontSeeText('Detergente descontinuado');
    }

    public function test_catalogo_muestra_categoria_descripcion_precio_y_stock_del_producto(): void
    {
        // Preparar el contenido informativo que la historia exige mostrar al cliente.
        $categoria = $this->crearCategoria('Desinfectantes', 'desinfectantes');
        $producto = $this->crearProducto($categoria, [
            'nombre' => 'Desinfectante cítrico',
            'descripcion' => 'Elimina bacterias en superficies lavables.',
            'precio_venta' => 22.75,
        ]);
        $this->asignarStock($producto, 8);

        $respuesta = $this->get(route('home'));

        // Comprobar nombre, categoría, descripción, precio formateado y suma de stock por almacén.
        $respuesta->assertOk()
            ->assertSeeText('Desinfectante cítrico')
            ->assertSeeText('Desinfectantes')
            ->assertSeeText('Elimina bacterias en superficies lavables.')
            ->assertSeeText('S/ 22.75')
            ->assertSee('src="http://localhost/images/producto-default.svg"', false)
            ->assertSeeText('Stock: 8 unidades');
    }

    public function test_productos_del_seeder_cargan_sus_imagenes_locales(): void
    {
        // Cargar el catálogo de demostración y revisar las rutas locales de sus imágenes.
        $this->seed(\Database\Seeders\CatalogoSeeder::class);

        $respuesta = $this->get(route('home'));

        $respuesta->assertOk()
            ->assertSee('src="http://localhost/images/products/detergente-industrial.jpg"', false)
            ->assertSee('src="http://localhost/images/products/lejia-multisuperficies.jpg"', false)
            ->assertSee('src="http://localhost/images/products/jabon-antibacterial.jpg"', false);
    }

    public function test_busqueda_muestra_productos_coincidentes_y_oculta_los_demas(): void
    {
        // Usar una descripción que tampoco contenga el término, para probar solo la coincidencia esperada.
        $categoria = $this->crearCategoria('Limpieza', 'limpieza');
        $this->crearProducto($categoria, ['nombre' => 'Detergente concentrado']);
        $this->crearProducto($categoria, [
            'nombre' => 'Lejía perfumada',
            'descripcion' => 'Blanqueador para ropa blanca.',
        ]);

        $respuesta = $this->get('/catalogo/buscar?q=Detergente');

        // La ruta debe conservar el término consultado y renderizar únicamente el resultado coincidente.
        $respuesta->assertOk()
            ->assertViewHas('q', 'Detergente')
            ->assertSeeText('Detergente concentrado')
            ->assertDontSeeText('Lejía perfumada');
    }

    public function test_filtro_por_categoria_muestra_solo_productos_de_la_categoria_seleccionada(): void
    {
        // Crear un producto por categoría y enviar el slug como selección del filtro.
        $categoriaSeleccionada = $this->crearCategoria('Desinfectantes', 'desinfectantes');
        $otraCategoria = $this->crearCategoria('Detergentes', 'detergentes');
        $this->crearProducto($categoriaSeleccionada, ['nombre' => 'Desinfectante de pino']);
        $this->crearProducto($otraCategoria, ['nombre' => 'Detergente enzimático']);

        $respuesta = $this->get(route('catalogo.buscar', [
            'categoria' => $categoriaSeleccionada->slug,
        ]));

        // El producto de la categoría solicitada debe aparecer; el de la otra categoría, no.
        $respuesta->assertOk()
            ->assertSeeText('Desinfectante de pino')
            ->assertDontSeeText('Detergente enzimático');
    }

    public function test_catalogo_vacio_no_muestra_productos_de_ejemplo_no_registrados(): void
    {
        // RefreshDatabase deja las tablas sin productos, simulando un catálogo recién iniciado.
        $respuesta = $this->get(route('home'));

        // Se espera el mensaje vacío y que no se mezclen tarjetas ficticias con datos de la base.
        $respuesta->assertOk()
            ->assertSeeText('No hay productos disponibles por el momento.')
            ->assertDontSeeText('Detergente Líquido Industrial 5 Litros')
            ->assertDontSeeText('Lejía Concentrada Multi-Superficies')
            ->assertDontSeeText('Jabón Líquido Antibacterial para Manos');
    }
}
