<?php

namespace Tests\Feature;

use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\StockPorAlmacen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeleccionarProductoFeatureTest extends TestCase
{
	use RefreshDatabase;

	private function crearProductoConStock(string $nombre, int $stock, float $precio = 15.50): Producto
	{
		// Se usa un identificador único para evitar colisiones con los slugs de categoría.
		$identificador = strtoupper(bin2hex(random_bytes(4)));
		$categoria = Categoria::create([
			'nombre' => "Limpieza {$identificador}",
			'slug' => 'limpieza-' . strtolower($identificador),
			'activo' => true,
		]);

		$producto = Producto::create([
			'codigo' => 'PROD-' . $identificador,
			'nombre' => $nombre,
			'descripcion' => "Descripción de {$nombre}",
			'categoria_id' => $categoria->id,
			'precio_venta' => $precio,
			'stock_minimo' => 1,
			'activo' => true,
		]);

		$almacen = Almacen::create([
			'nombre' => 'Almacén principal',
			'direccion' => 'Cusco',
			'activo' => true,
		]);

		StockPorAlmacen::create([
			'producto_id' => $producto->id,
			'almacen_id' => $almacen->id,
			'cantidad' => $stock,
		]);

		// Cada escenario recibe un producto registrado con inventario real en la base de prueba.
		return $producto;
	}

	public function test_cliente_selecciona_un_producto_disponible_del_catalogo(): void
	{
		// Preparar un producto visible en catálogo y confirmar que puede agregarse al carrito.
		$producto = $this->crearProductoConStock('Detergente líquido', 10);

		$this->get(route('home'))
			->assertOk()
			->assertSeeText('Detergente líquido')
			->assertSee(route('carrito.agregar'), false);

		$respuesta = $this->from(route('home'))->post(route('carrito.agregar'), [
			'producto_id' => $producto->id,
			'cantidad' => 2,
		]);

		// Comprobar que la sesión guarda cantidad y subtotal calculado.
		$respuesta->assertRedirect(route('home'))
			->assertSessionHas("carrito.{$producto->id}.cantidad", 2)
			->assertSessionHas("carrito.{$producto->id}.subtotal", 31.00);
	}

	public function test_cliente_puede_seleccionar_cantidad_menor_al_stock(): void
	{
		// El stock es 20; solicitar 3 unidades debe aceptarse.
		$producto = $this->crearProductoConStock('Desinfectante', 20);

		$this->post(route('carrito.agregar'), [
			'producto_id' => $producto->id,
			'cantidad' => 3,
		])
			->assertRedirect()
			->assertSessionHas("carrito.{$producto->id}.cantidad", 3);
	}

	public function test_rechaza_seleccionar_mas_unidades_que_el_stock_disponible(): void
	{
		// El stock es 2; solicitar 5 unidades debe generar un error sin guardar el producto.
		$producto = $this->crearProductoConStock('Lejía concentrada', 2);

		$this->from(route('home'))->post(route('carrito.agregar'), [
			'producto_id' => $producto->id,
			'cantidad' => 5,
		])
			->assertRedirect(route('home'))
			->assertSessionHasErrors('cantidad')
			->assertSessionMissing("carrito.{$producto->id}");
	}

	public function test_actualiza_la_cantidad_y_el_subtotal_del_producto(): void
	{
		// Iniciar con una unidad en la sesión y solicitar una nueva cantidad dentro del stock.
		$producto = $this->crearProductoConStock('Jabón líquido', 10);

		$this->withSession([
			'carrito' => [
				$producto->id => [
					'id' => $producto->id,
					'nombre' => $producto->nombre,
					'codigo' => $producto->codigo,
					'categoria' => 'Limpieza',
					'precio' => 15.50,
					'cantidad' => 1,
					'subtotal' => 15.50,
					'url_imagen' => '',
				],
			],
		])->put(route('carrito.actualizar'), [
			'producto_id' => $producto->id,
			'cantidad' => 4,
		])
			// Verificar que la cantidad nueva y su subtotal reemplazan los valores anteriores.
			->assertRedirect(route('carrito.index'))
			->assertSessionHas("carrito.{$producto->id}.cantidad", 4)
			->assertSessionHas("carrito.{$producto->id}.subtotal", 62.00);
	}

	public function test_rechaza_modificar_la_cantidad_por_encima_del_stock(): void
	{
		// Mantener 2 unidades en sesión si se intenta cambiar a 6 con stock de solo 5.
		$producto = $this->crearProductoConStock('Limpiador multiusos', 5);

		$this->withSession([
			'carrito' => [
				$producto->id => [
					'id' => $producto->id,
					'nombre' => $producto->nombre,
					'codigo' => $producto->codigo,
					'categoria' => 'Limpieza',
					'precio' => 15.50,
					'cantidad' => 2,
					'subtotal' => 31.00,
					'url_imagen' => '',
				],
			],
		])->put(route('carrito.actualizar'), [
			'producto_id' => $producto->id,
			'cantidad' => 6,
		])
			->assertRedirect()
			->assertSessionHasErrors('cantidad')
			->assertSessionHas("carrito.{$producto->id}.cantidad", 2);
	}

	public function test_elimina_un_producto_seleccionado_del_carrito(): void
	{
		// Simular un carrito con un producto y comprobar que la ruta DELETE lo elimina.
		$producto = $this->crearProductoConStock('Esponja abrasiva', 8);

		$this->withSession([
			'carrito' => [
				$producto->id => [
					'id' => $producto->id,
					'nombre' => $producto->nombre,
					'codigo' => $producto->codigo,
					'categoria' => 'Limpieza',
					'precio' => 15.50,
					'cantidad' => 1,
					'subtotal' => 15.50,
					'url_imagen' => '',
				],
			],
		])->delete(route('carrito.eliminar', $producto->id))
			->assertRedirect(route('carrito.index'))
			->assertSessionHas('carrito', []);
	}

	public function test_muestra_resumen_con_los_productos_y_cantidades_seleccionadas(): void
	{
		// Cargar dos productos con cantidades distintas para comprobar el resumen total.
		$primerProducto = $this->crearProductoConStock('Detergente en polvo', 10, 12.00);
		$segundoProducto = $this->crearProductoConStock('Desinfectante de pino', 6, 8.50);

		$this->withSession([
			'carrito' => [
				$primerProducto->id => [
					'id' => $primerProducto->id,
					'nombre' => $primerProducto->nombre,
					'codigo' => $primerProducto->codigo,
					'categoria' => 'Limpieza',
					'precio' => 12.00,
					'cantidad' => 2,
					'subtotal' => 24.00,
					'url_imagen' => '',
				],
				$segundoProducto->id => [
					'id' => $segundoProducto->id,
					'nombre' => $segundoProducto->nombre,
					'codigo' => $segundoProducto->codigo,
					'categoria' => 'Limpieza',
					'precio' => 8.50,
					'cantidad' => 3,
					'subtotal' => 25.50,
					'url_imagen' => '',
				],
			],
		])->get(route('carrito.index'))
			->assertOk()
			->assertSeeText('Detergente en polvo')
			->assertSeeText('Desinfectante de pino')
			->assertSee('value="2"', false)
			->assertSee('value="3"', false)
			->assertSeeText('S/ 49.50');
	}

	public function test_actualiza_la_imagen_obsoleta_del_carrito_desde_el_producto(): void
	{
		// Simular una sesión creada antes de que el producto tuviera una imagen local.
		$producto = $this->crearProductoConStock('Detergente industrial', 10);
		$producto->update(['imagen' => 'images/products/detergente-industrial.jpg']);
		$urlImagenActual = asset('images/products/detergente-industrial.jpg');

		$this->withSession([
			'carrito' => [
				$producto->id => [
					'id' => $producto->id,
					'nombre' => $producto->nombre,
					'codigo' => $producto->codigo,
					'categoria' => 'Limpieza',
					'precio' => 15.50,
					'cantidad' => 1,
					'subtotal' => 15.50,
					'url_imagen' => asset('images/producto-default.png'),
				],
			],
		])->get(route('carrito.index'))
			->assertOk()
			->assertSee('src="' . $urlImagenActual . '"', false)
			->assertDontSee('producto-default.png', false)
			->assertSessionHas("carrito.{$producto->id}.url_imagen", $urlImagenActual);
	}
}

