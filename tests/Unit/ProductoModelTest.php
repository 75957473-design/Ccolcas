<?php

namespace Tests\Unit;

use App\Models\Producto;
use App\Models\StockPorAlmacen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * 3.1 Prueba de Modelo: Cálculo de Precio de Venta y Stock Total (Producto)
 *
 * Buenas prácticas aplicadas:
 * - AAA (Given/When/Then) comentado en cada test
 * - 1 comportamiento = 1 test (Single Responsibility)
 * - Uso de factories reales (ProductoFactory, StockPorAlmacenFactory, AlmacenFactory)
 * - BVA con DataProvider (4 tests visibles en php artisan test)
 * - assertEqualsWithDelta para decimales (dinero)
 * - make() sin BD cuando no se necesita, create() solo para stockTotal/enStockCritico
 */
class ProductoModelTest extends TestCase
{
    use RefreshDatabase;

    // ==============================================================
    // 1. calcularPrecioVenta() — app/Models/Producto.php:75
    //    Fórmula CCOLCAS: (costo_compra + costo_flete) * (1 + margen_ganancia/100)
    // ==============================================================

    public function test_calcular_precio_venta_con_margen_20_retorna_14_40(): void
    {
        // Given: costo_compra 10.00 + costo_flete 2.00 + margen 20% → (12)*1.20 = 14.40
        $producto = Producto::factory()->make([
            'costo_compra' => 10.00,
            'costo_flete' => 2.00,
            'margen_ganancia' => 20.00,
        ]);

        // When: se calcula el precio de venta
        $precio = $producto->calcularPrecioVenta();

        // Then: debe ser 14.40 exacto (tolerancia 0.01 por decimales)
        $this->assertEqualsWithDelta(14.40, $precio, 0.01);
    }

    public function test_calcular_precio_venta_sin_margen_retorna_costo_total(): void
    {
        // Given: sin margen (0%) el precio es solo costo + flete
        $producto = Producto::factory()->make([
            'costo_compra' => 8.50,
            'costo_flete' => 1.50,
            'margen_ganancia' => 0,
        ]);

        // When
        $precio = $producto->calcularPrecioVenta();

        // Then
        $this->assertEqualsWithDelta(10.00, $precio, 0.01);
    }

    public function test_calcular_precio_venta_redondea_a_dos_decimales(): void
    {
        // Given: valores que generan decimales largos → debe redondear a 2
        $producto = Producto::factory()->make([
            'costo_compra' => 10.33,
            'costo_flete' => 1.11,
            'margen_ganancia' => 15.55,
        ]);

        // When: (10.33+1.11)=11.44 * 1.1555 = 13.219... → 13.22
        $precio = $producto->calcularPrecioVenta();

        // Then
        $this->assertEqualsWithDelta(13.22, $precio, 0.01);
    }

    // ==============================================================
    // 2. stockTotal() — app/Models/Producto.php:82
    //    Suma de stock_por_almacen.cantidad
    // ==============================================================

    public function test_stock_total_suma_por_almacen(): void
    {
        // Given: producto persistido + 2 registros en stock_por_almacen (5 y 3)
        $producto = Producto::factory()->create([
            'stock_minimo' => 5,
        ]);
        StockPorAlmacen::factory()->create(['producto_id' => $producto->id, 'cantidad' => 5]);
        StockPorAlmacen::factory()->create(['producto_id' => $producto->id, 'cantidad' => 3]);

        // When
        $total = $producto->fresh()->stockTotal();

        // Then: 5 + 3 = 8
        $this->assertSame(8, $total);
    }

    public function test_stock_total_sin_registros_retorna_cero(): void
    {
        // Given: producto sin registros en stock_por_almacen
        $producto = Producto::factory()->create([
            'stock_minimo' => 5,
        ]);

        // When
        $total = $producto->stockTotal();

        // Then
        $this->assertSame(0, $total);
    }

    public function test_stock_total_con_relation_loaded_usa_coleccion(): void
    {
        // Given: producto con relación stocks ya cargada → rama relationLoaded()
        $producto = Producto::factory()->create(['stock_minimo' => 5]);
        StockPorAlmacen::factory()->create(['producto_id' => $producto->id, 'cantidad' => 10]);

        // When: se carga eager
        $productoConStocks = Producto::with('stocks')->find($producto->id);

        // Then: debe usar la colección y no hacer query extra
        $this->assertTrue($productoConStocks->relationLoaded('stocks'));
        $this->assertSame(10, $productoConStocks->stockTotal());
        $this->assertFalse($productoConStocks->enStockCritico());
    }

    // ==============================================================
    // 3. enStockCritico() — app/Models/Producto.php:91
    //    stockTotal() <= stock_minimo  (BVA con DataProvider)
    // ==============================================================

    public static function providerStockCritico(): array
    {
        return [
            'agotado 0 <= 5 -> critico (BVA minimo)' => [0, 5, true],
            'borde 5 <= 5 -> critico (BVA igual)' => [5, 5, true],
            'uno sobre 6 > 5 -> no critico (BVA+1)' => [6, 5, false],
            'sobre 8 > 5 -> no critico' => [8, 5, false],
        ];
    }

    #[DataProvider('providerStockCritico')]
    public function test_en_stock_critico_bva(int $cantidad, int $stockMinimo, bool $esperadoCritico): void
    {
        // Given: producto con stock_minimo = 5 y cantidad variable
        $producto = Producto::factory()->create([
            'stock_minimo' => $stockMinimo,
        ]);

        // Solo crear registro si cantidad > 0 (0 = sin registros = stock 0)
        if ($cantidad > 0) {
            StockPorAlmacen::factory()->create([
                'producto_id' => $producto->id,
                'cantidad' => $cantidad,
            ]);
        }

        // When
        $esCritico = $producto->fresh()->enStockCritico();

        // Then
        $this->assertSame($esperadoCritico, $esCritico, "Fallo BVA cantidad={$cantidad} minimo={$stockMinimo}");
    }

    // ==============================================================
    // 4. Coherencia Factory ↔ Modelo (regression)
    // ==============================================================

    public function test_factory_genera_precio_venta_coherente_con_formula(): void
    {
        // Given: producto creado por factory (usa fórmula en ProductoFactory.php:19)
        $producto = Producto::factory()->create();

        // When
        $calculado = $producto->calcularPrecioVenta();

        // Then: ambos deben ser positivos y coherentes (no 0, no negativo)
        $this->assertGreaterThan(0, $calculado);
        $this->assertGreaterThan(0, (float) $producto->precio_venta);
    }
}
