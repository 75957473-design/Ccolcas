# 03 — Base de Datos

> CCOLCAS — MySQL `ccolcasdb` (prod) / SQLite `:memory:` (tests) · Laravel 12 · 19 entidades
> Repositorio: https://github.com/75957473-design/Colcas.git

Migraciones en `database/migrations/` (5 archivos):
`0001_01_01_000000_create_users_table.php` (19 tablas) + `0001_01_01_000001_create_cache_table.php` + `0001_01_01_000002_create_jobs_table.php` + `2026_10_12_000000_create_historial_pedido.php` + `2026_10_12_100000_add_datos_to_bitacora.php`.

Config: `.env` `DB_DATABASE=ccolcasdb`, `BCRYPT_ROUNDS=12` · Tests: `phpunit.xml` fuerza `sqlite :memory:` y `BCRYPT_ROUNDS=4`.

---

## 1. Conexión

```env
# .env / .env.example
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ccolcasdb
DB_USERNAME=root
DB_PASSWORD=
BCRYPT_ROUNDS=12
```

```php
// config/database.php — MySQL usa env('DB_DATABASE','ccolcasdb')
// config/auth.php — provider users: driver eloquent, model App\Models\Usuario (tabla usuarios)
```

Creación:

```bash
# phpMyAdmin http://localhost/phpmyadmin -> Crear BD ccolcasdb utf8mb4_unicode_ci
php -r "new PDO('mysql:host=127.0.0.1','root','')->exec('CREATE DATABASE ccolcasdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');"
php artisan migrate --force          # 5 migraciones
php artisan db:seed --force          # RolesSeeder -> EstadosPedidoSeeder -> CatalogoSeeder
php artisan migrate:status           # verifica "Ran"
```

Tests usan `sqlite :memory:` automático (ver `phpunit.xml` y `docs/01-instalacion.md`).

Opcional — diagrama ER con Graphviz:

```bash
php artisan generate:erd docs/diagrama-er.png --format=png
```

---

## 2. Diagrama ER (resumen)

```
unidades_medida 1--* productos *--1 categorias
productos 1--* stock_por_almacen *--1 almacenes
roles 1--* usuarios 1--0..1 clientes 1--* pedidos 1--* detalle_pedidos *--1 productos
estados_pedido 1--* pedidos 1--0..1 ventas
pedidos 1--* historial_pedido *--1 estados_pedido / usuarios
proveedores 1--* compras 1--* detalle_compras *--1 productos / almacenes
tipos_movimiento 1--* movimientos_inventario *--1 productos / almacenes / usuarios
usuarios 1--* bitacora_cambios
usuarios 1--* historial_pedido
```

> El ER completo está en PA3 y en el Entregable Final (con captura fechada).
> Si generas `docs/diagrama-er.png`, insértalo aquí: `![ER](diagrama-er.png)`

---

## 3. Tablas (19 de dominio + auxiliares Laravel)

| # | Tabla | Columnas clave | Notas |
|---|---|---|---|
| 1 | **unidades_medida** | `id, nombre unique, simbolo` | Catálogo de unidades |
| 2 | **categorias** | `id, nombre unique, slug unique, activo` | Toggle activo/inactivo |
| 3 | **productos** | `id, codigo unique, nombre, descripcion, imagen, categoria_id FK, unidad_medida_id FK, costo_compra decimal, costo_flete decimal, margen_ganancia decimal, precio_venta decimal, stock_minimo, activo, timestamps` | `precio_venta = (costo_compra + costo_flete) * (1 + margen%)` calculado en modelo/controlador; `admin.js` lo sugiere en vivo |
| 4 | **almacenes** | `id, nombre, direccion, activo` | Multialmacén |
| 5 | **stock_por_almacen** | `id, producto_id FK cascade, almacen_id FK cascade, cantidad, unique(producto_id, almacen_id)` | Stock desagregado; usado con `lockForUpdate` |
| 6 | **roles** | `id, nombre unique, descripcion` | 7 roles (ver Seeds) |
| 7 | **usuarios** | `id, nombre_completo, rol_id FK, email unique, password hashed BCRYPT_ROUNDS=12, activo, rememberToken, timestamps` | `casts ['password'=>'hashed']`, `Hash::make/check` |
| 8 | **clientes** | `id, usuario_id FK nullable set null, tipo_documento enum DNI/RUC, numero_documento unique, nombre_razon_social, telefono, email, direccion_entrega, persona_recepcion, timestamps` | 1 cliente por usuario Cliente |
| 9 | **proveedores** | `id, ruc unique, nombre, contacto, telefono, email, direccion, timestamps` | Para compras/abastecimiento |
| 10 | **compras** | `id, proveedor_id FK, usuario_id FK, numero_comprobante, fecha_compra, total, observaciones, timestamps` | Cabecera de compra |
| 11 | **detalle_compras** | `id, compra_id FK cascade, producto_id FK, almacen_id FK, cantidad, precio_unitario, fecha_vencimiento` | Detalle + almacén destino |
| 12 | **estados_pedido** | `id, nombre` | 6 estados (ver Seeds) |
| 13 | **pedidos** | `id, cliente_id FK, estado_id FK, vendedor_id FK nullable, tipo_comprobante_solicitado enum Boleta/Factura, fecha_pedido, monto_total decimal, direccion_envio, observaciones, timestamps` | `vendedor_id` solo en Confirmado |
| 14 | **detalle_pedidos** | `id, pedido_id FK cascade, producto_id FK, almacen_id FK nullable, cantidad, precio_unitario, subtotal` | Snapshot de precio al pedir |
| 15 | **ventas** | `id, pedido_id FK unique, usuario_id FK, tipo_comprobante enum Boleta/Factura/Nota de Venta, numero_comprobante, metodo_pago enum Efectivo/Transferencia/YapePlin, monto_pagado, fecha_emision, timestamps` | 1 venta por pedido; descuenta stock |
| 16 | **tipos_movimiento** | `id, nombre unique, tipo enum ENTRADA/SALIDA` | Catálogo de movimientos |
| 17 | **movimientos_inventario** | `id, producto_id FK, almacen_id FK, tipo_movimiento_id FK, cantidad, motivo, usuario_id FK, fecha_movimiento` | Trazabilidad; transferencia genera 2 filas |
| 18 | **historial_pedido** | `id, pedido_id FK cascade, estado_anterior_id FK nullable, estado_nuevo_id FK, usuario_id FK nullable, observacion text nullable, timestamps` | Stepper/timeline auditable (PA3 flujo por roles) |
| 19 | **bitacora_cambios** | `id, usuario_id FK, tabla_afectada, accion enum INSERT/UPDATE/DELETE, descripcion, datos_previos json nullable, datos_nuevos json nullable, fecha` | Auditoría ISO 25010 vía `AuditObserver` |

**Auxiliares Laravel** (no de dominio): `password_reset_tokens`, `sessions`, `cache`, `jobs`, `migrations`, `job_batches`, `failed_jobs`.

---

## 4. Seeds

| Seeder | Qué carga | Clave |
|---|---|---|
| **RolesSeeder** | 7 roles: `Administrador, Gerente, Subgerente, Contador, Almacenero, Vendedor, Cliente` | `firstOrCreate` por nombre |
| **EstadosPedidoSeeder** | 6 estados: `Pendiente, Confirmado, En Almacén, Enviado, Entregado, Cancelado` | `firstOrCreate` por nombre |
| **CatalogoSeeder** | 1 almacén principal + 3 categorías + 3 productos demo + `stock_por_almacen` inicial + `tipos_movimiento` | Datos de prueba |
| **DatabaseSeeder** | `RolesSeeder -> EstadosPedidoSeeder -> CatalogoSeeder` | Orden con dependencias |

```bash
php artisan db:seed --force
php artisan db:seed --class=RolesSeeder --force
php artisan db:seed --class=EstadosPedidoSeeder --force
php artisan db:seed --class=CatalogoSeeder --force
```

---

## 5. Factories (19)

Una por modelo en `database/factories/` — incluye `HistorialPedidoFactory` y `BitacoraCambioFactory` — para tests con `RefreshDatabase` y datos realistas (Faker). Los tests usan `sqlite :memory:` y `RefreshDatabase` sin tocar MySQL.

---

## 6. Relaciones clave (Eloquent)

- `Producto belongsTo Categoria, UnidadMedida; hasMany StockPorAlmacen, DetallePedido, MovimientoInventario`
- `StockPorAlmacen belongsTo Producto, Almacen`
- `Rol hasMany Usuario; Usuario belongsTo Rol; hasOne Cliente`
- `Cliente belongsTo Usuario; hasMany Pedido`
- `Pedido belongsTo Cliente, EstadoPedido, Usuario(vendedor); hasMany DetallePedido, HistorialPedido; hasOne Venta`
- `HistorialPedido belongsTo Pedido, EstadoPedido(anterior/nuevo), Usuario`
- `Venta belongsTo Pedido, Usuario`
- `MovimientoInventario belongsTo Producto, Almacen, TipoMovimiento, Usuario`

Ver `app/Models/*.php` y `docs/02-arquitectura.md` §4-5.

---

## 7. Notas de integridad

- `stock_por_almacen` con `unique(producto_id, almacen_id)` + `lockForUpdate` en `PedidoController@store`, `VentaController@store`, `InventarioController@movimiento/ajustar/transferir`.
- `ventas.pedido_id unique` — un pedido solo se factura una vez.
- `AuditObserver` sobre `Producto, Pedido, Venta` → `bitacora_cambios` con `datos_previos/nuevos` JSON (ver `app/Observers/AuditObserver.php` y `AppServiceProvider::boot()`).
