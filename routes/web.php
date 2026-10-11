<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterClienteController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\PedidoAdminController;
use App\Http\Controllers\Admin\VentaController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\UsuarioController;

// Página Principal y Búsqueda
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalogo/buscar', [HomeController::class, 'buscar'])->name('catalogo.buscar');

// Carrito de Compras (Sesión)
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
Route::put('/carrito/actualizar', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');

// Autenticación Generales (Login / Logout)
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Registro Exclusivo de Clientes
Route::post('/cliente/registro', [RegisterClienteController::class, 'store'])->name('cliente.register');

// Rutas Protegidas según Perfil
Route::middleware(['auth'])->group(function () {
    // Área de Clientes (Flujo de Pedidos) — confirmación 100% web
    Route::get('/mi-cuenta/pedidos', [PedidoController::class, 'index'])->name('cliente.mis-pedidos');
    Route::get('/mi-cuenta/pedidos/{pedido}', [PedidoController::class, 'show'])->name('cliente.pedido.show');
    Route::get('/checkout', [PedidoController::class, 'checkout'])->name('pedidos.checkout');
    Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store');

    // Panel Administrativo para Trabajadores (todos los roles excepto Cliente)
    Route::prefix('admin')->name('admin.')->middleware('role:Administrador,Gerente,Subgerente,Contador,Almacenero,Vendedor')->group(function () {
        Route::get('/dashboard', [HomeController::class, 'dashboardAdmin'])->name('dashboard');

        // Productos
        Route::resource('productos', ProductoController::class)->except(['show']);
        Route::patch('productos/{producto}/toggle', [ProductoController::class, 'toggle'])->name('productos.toggle');

        // Categorías
        Route::get('categorias', [CategoriaController::class, 'index'])->name('categorias.index');
        Route::post('categorias', [CategoriaController::class, 'store'])->name('categorias.store');
        Route::put('categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
        Route::delete('categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
        Route::patch('categorias/{categoria}/toggle', [CategoriaController::class, 'toggle'])->name('categorias.toggle');

        // Inventario — Control de existencias (ingresos/salidas/transferencias multialmacén)
        Route::get('inventario', [InventarioController::class, 'index'])->name('inventario.index');
        Route::post('inventario/movimiento', [InventarioController::class, 'movimiento'])->name('inventario.movimiento');
        Route::post('inventario/ajustar', [InventarioController::class, 'ajustar'])->name('inventario.ajustar');
        Route::post('inventario/transferir', [InventarioController::class, 'transferir'])->name('inventario.transferir');

        // Pedidos (admin)
        Route::get('pedidos', [PedidoAdminController::class, 'index'])->name('pedidos.index');
        Route::get('pedidos/{pedido}', [PedidoAdminController::class, 'show'])->name('pedidos.show');
        Route::patch('pedidos/{pedido}/estado', [PedidoAdminController::class, 'updateEstado'])->name('pedidos.estado');

        // Ventas
        Route::get('ventas', [VentaController::class, 'index'])->name('ventas.index');
        Route::get('ventas/crear', [VentaController::class, 'create'])->name('ventas.create');
        Route::post('ventas', [VentaController::class, 'store'])->name('ventas.store');
        Route::get('ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');

        // Clientes
        Route::get('clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
        Route::get('clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
        Route::put('clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');

        // Usuarios y Roles (solo Administrador y Gerente)
        Route::middleware('role:Administrador,Gerente')->group(function () {
            Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
            Route::patch('usuarios/{usuario}/toggle', [UsuarioController::class, 'toggle'])->name('usuarios.toggle');
        });
    });
});
