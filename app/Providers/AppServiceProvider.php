<?php

namespace App\Providers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Venta;
use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * Auditoría y Trazabilidad: Observer pasivo -> bitacora_cambios
     */
    public function boot(): void
    {
        // Auditoría automática de entidades críticas (datos_previos/datos_nuevos + usuario + timestamp)
        Producto::observe(AuditObserver::class);
        Pedido::observe(AuditObserver::class);
        Venta::observe(AuditObserver::class);
    }
}
