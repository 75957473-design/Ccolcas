<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. UNIDADES DE MEDIDA Y CATEGORÍAS
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('simbolo', 10);
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->boolean('activo')->default(true);
        });

        // 2. PRODUCTOS Y PRECIOS
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique()->nullable();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->foreignId('unidad_medida_id')->nullable()->constrained('unidades_medida')->onDelete('set null');
            
            // Factores de Precio (Requerimiento CCOLCAS)
            $table->decimal('costo_compra', 10, 2)->default(0.00);
            $table->decimal('costo_flete', 10, 2)->default(0.00);
            $table->decimal('margen_ganancia', 5, 2)->default(0.00); // Porcentaje
            $table->decimal('precio_venta', 10, 2)->default(0.00);
            
            $table->integer('stock_minimo')->default(5);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 3. ALMACENES Y CONTROL DE STOCK
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('direccion')->nullable();
            $table->boolean('activo')->default(true);
        });

        Schema::create('stock_por_almacen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade');
            $table->integer('cantidad')->default(0);
            $table->unique(['producto_id', 'almacen_id']); // Evita registros duplicados
        });

        // 4. ROLES Y USUARIOS
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion')->nullable();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo', 100);
            $table->foreignId('rol_id')->constrained('roles');
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // 5. CLIENTES
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->enum('tipo_documento', ['DNI', 'RUC'])->default('DNI');
            $table->string('numero_documento', 15)->unique();
            $table->string('nombre_razon_social', 150);
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion_entrega')->nullable();
            $table->string('persona_recepcion', 100)->nullable();
            $table->timestamps();
        });

        // 6. PROVEEDORES Y COMPRAS
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('ruc', 11)->unique()->nullable();
            $table->string('nombre', 100);
            $table->string('contacto', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->timestamps();
        });

        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->string('numero_comprobante', 50)->nullable();
            $table->dateTime('fecha_compra')->useCurrent();
            $table->decimal('total', 10, 2)->default(0.00);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->date('fecha_vencimiento')->nullable();
        });

        // 7. PEDIDOS Y VENTAS
        Schema::create('estados_pedido', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50); // ej: Pendiente, Confirmado, En Almacen, Enviado, Entregado, Cancelado
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('estado_id')->constrained('estados_pedido');
            $table->foreignId('vendedor_id')->nullable()->constrained('usuarios');
            $table->enum('tipo_comprobante_solicitado', ['Boleta', 'Factura'])->default('Boleta');
            $table->dateTime('fecha_pedido')->useCurrent();
            $table->decimal('monto_total', 10, 2);
            $table->text('direccion_envio');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->unique()->constrained('pedidos');
            $table->foreignId('usuario_id')->constrained('usuarios'); // Cajero/Vendedor que procesó la venta final
            $table->enum('tipo_comprobante', ['Boleta', 'Factura', 'Nota de Venta']);
            $table->string('numero_comprobante', 20);
            $table->enum('metodo_pago', ['Efectivo', 'Transferencia', 'Yape/Plin'])->default('Efectivo');
            $table->decimal('monto_pagado', 10, 2);
            $table->dateTime('fecha_emision')->useCurrent();
            $table->timestamps();
        });

        // 8. MOVIMIENTOS E INVENTARIO
        Schema::create('tipos_movimiento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique(); // Entrada por Compra, Salida por Venta, Ajuste, Merma
            $table->enum('tipo', ['ENTRADA', 'SALIDA']);
        });

        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->foreignId('tipo_movimiento_id')->constrained('tipos_movimiento');
            $table->integer('cantidad');
            $table->text('motivo')->nullable();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->dateTime('fecha_movimiento')->useCurrent();
        });

        Schema::create('bitacora_cambios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->string('tabla_afectada', 50);
            $table->string('accion', 20); // INSERT, UPDATE, DELETE
            $table->text('descripcion');
            $table->dateTime('fecha')->useCurrent();
        });

        // Tablas auxiliares de Laravel
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('bitacora_cambios');
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('tipos_movimiento');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('detalle_pedidos');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('estados_pedido');
        Schema::dropIfExists('detalle_compras');
        Schema::dropIfExists('compras');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('stock_por_almacen');
        Schema::dropIfExists('almacenes');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('unidades_medida');
    }
};
