# 02 — Arquitectura

> CCOLCAS — Sistema Web de Gestión de Inventario, Almacén y Ventas
> PA1 §6 / PA3 — Sede Cusco 2026 · Repositorio: https://github.com/75957473-design/Colcas.git
> Stack: **Laravel 12 · PHP 8.2 · MySQL `ccolcasdb`** (prod) / **SQLite `:memory:`** (tests) · **Vite 7 · Tailwind 4 · Blade · Eloquent ORM**

---

## 1. ¿Qué tipo de sistema es?

**Aplicación web interactiva** (no app nativa ni híbrida móvil). Se usa desde el navegador:

- **Cliente** (lo que ves): HTML + Blade, CSS separado por área, Tailwind 4, JavaScript.
- **Servidor** (lo que no ves): PHP 8.2 + Laravel 12 que valida, calcula y guarda en MySQL `ccolcasdb`.
- **Entorno local**: XAMPP (Apache + MySQL + phpMyAdmin).

> **¿Y lo híbrido?** En arquitectura DHARMA, sí es un **sistema híbrido**: combina componentes COTS/FOSS (Laravel, MySQL, Vite, Tailwind), servicios web y código a medida (flujo de pedidos por rol, auditoría, transferencias multialmacén). Eso justifica hacerlo **incremental** y con capas separadas (OOHDM / MVC).

---

## 2. Metodología — OOHDM (PA3)

Elegida porque CCOLCAS tiene zona pública (catálogo) y zona privada por rol (panel admin).

| Fase OOHDM | Qué hicimos en CCOLCAS |
|---|---|
| **1. Diseño Conceptual** | Modelo de 19 entidades: productos, categorías, almacenes, stock_por_almacen, roles, usuarios, clientes, pedidos, detalle_pedidos, estados_pedido, historial_pedido, ventas, compras, proveedores, movimientos, bitacora_cambios, etc. |
| **2. Diseño Navegacional** | Mapa de nodos: público (`/` catálogo, `/carrito`) vs cliente autenticado (`/checkout`, `/mi-cuenta/pedidos`) vs admin por rol (`/admin/*`). Ver §6 Rutas. |
| **3. Diseño de Interfaz Abstracta (ADV)** | Vistas Blade + Tailwind, layout público vs `layouts/admin.blade.php` + CSS/JS separados por área (ver §4). |
| **4. Implementación** | Laravel 12 MVC + Eloquent sobre XAMPP Apache/MySQL, con `ccolcasdb` y `BCRYPT_ROUNDS=12`. |

**Estrategia incremental** (4 incrementos): infra/entorno → persistencia+seguridad → catálogo/almacén → pedidos/ventas/auditoría.
**Evaluación tripartita** (PA3): heurística Nielsen (10 principios) + pruebas funcionales (stock, precios por rol, descuento venta) + SUS (8-12 usuarios, umbral 68).

---

## 3. Stack detallado

| Capa | Tecnología | Uso en CCOLCAS |
|---|---|---|
| Presentación | HTML5 + Blade, Tailwind 4, CSS por área (`admin.css`, `pedidos.css`, `inicio.css`, `carrito.css`) | Catálogo, carrito, checkout, seguimiento, panel admin |
| Interacción | JavaScript (`admin.js`, `inicio.js`, `carrito.js`), Vite 7 | Cálculo precio en vivo, confirmaciones, UX |
| Backend | PHP 8.2 + Laravel 12 (MVC) | Validación, transacciones, workflow por rol |
| Persistencia | MySQL `ccolcasdb` (prod) / SQLite `:memory:` (tests), Eloquent ORM | 19 tablas + auxiliares, `lockForUpdate` en operaciones críticas |
| Infra | XAMPP (Apache+MySQL), Composer, Node, Git, GitHub | Desarrollo local y versionado |
| Seguridad | BCRYPT 12, `@csrf`, `RoleMiddleware` alias `role`, validación, auditoría | RBAC 7 roles, bitácora, historial |

---

## 4. Patrón MVC + capas

```
app/Models/*               -> Eloquent (belongsTo/hasMany/hasOne) — 19 entidades
app/Http/Controllers/*      -> Recibe request, valida, orquesta transacciones, responde
  + Admin/*  (7)           -> Producto, Categoria, Inventario, PedidoAdmin, Venta, Cliente, Usuario
  + Auth/*   (2)           -> Login, RegisterCliente
  + HomeController, CarritoController, PedidoController
app/Http/Middleware/        -> RoleMiddleware.php (alias 'role' en bootstrap/app.php)
app/Observers/*             -> AuditObserver -> bitacora_cambios (datos_previos/datos_nuevos)
app/Services/*              -> 11 Services de dominio + PedidoWorkflowService (máquina de estados)
app/Contracts/*             -> 7 interfaces de repositorios (mockeables en tests)
resources/views/*           -> Blade: welcome, carrito, cliente/{checkout,pedidos,pedido-show}, admin/*
resources/css/*             -> app.css, inicio.css, carrito.css, pedidos.css, admin.css
resources/js/*              -> app.js, inicio.js, carrito.js, admin.js
```

**Separación por área (PA3):** cada zona tiene su CSS/JS desacoplado vía Vite (`vite.config.js` 9 entradas: 5 CSS + 4 JS).

---

## 5. Estructura del proyecto

```
app/
  Models/ (19)          -> Usuario, Rol, Producto, Categoria, UnidadMedida, Almacen,
                           StockPorAlmacen, Cliente, Pedido, DetallePedido, EstadoPedido,
                           HistorialPedido, Venta, Proveedor, Compra, DetalleCompra,
                           TipoMovimiento, MovimientoInventario, BitacoraCambio
  Http/Controllers/ (13)
    HomeController, CarritoController, PedidoController,
    Auth/LoginController, Auth/RegisterClienteController,
    Admin/{Producto,Categoria,Inventario,PedidoAdmin,Venta,Cliente,Usuario}Controller
  Http/Middleware/      -> RoleMiddleware.php
  Services/ (12)        -> CatalogoService, PedidoService, InventarioService, VentaService,
                           SeleccionProductoService, PedidoWorkflowService, ...
  Contracts/ (7)        -> Interfaces de repositorios
  Observers/            -> AuditObserver.php
  Providers/            -> AppServiceProvider.php (Producto/Pedido/Venta::observe)
resources/
  views/                -> welcome.blade.php, carrito.blade.php,
                           cliente/{checkout,pedidos,pedido-show}.blade.php,
                           admin/{dashboard,productos,categorias,inventario,pedidos,ventas,clientes,usuarios}/*.blade.php,
                           layouts/{app,admin}.blade.php
  css/                  -> app.css, inicio.css, carrito.css, pedidos.css, admin.css
  js/                   -> app.js, inicio.js, carrito.js, admin.js
routes/web.php          -> 6 rutas públicas + 4 cliente (auth) + 22+ admin
database/
  migrations/ (5)       -> 0001_01_01_000000_create_users_table.php (19 tablas)
                           0001_01_01_000001_create_cache_table.php
                           0001_01_01_000002_create_jobs_table.php
                           2026_10_12_000000_create_historial_pedido.php
                           2026_10_12_100000_add_datos_to_bitacora.php
  seeders/              -> RolesSeeder (7 roles), EstadosPedidoSeeder (6 estados), CatalogoSeeder, DatabaseSeeder
  factories/ (19)       -> Una por modelo (incl. HistorialPedido, BitacoraCambio)
tests/
  Feature (3 suites)    -> CatalogoFeature, LoginFeature, SeleccionarProductoFeature
  Unit (12 suites)      -> ProductoModel, PedidoService, InventarioService, VentaService, ...
  Total: 168 tests (335 assertions)
```

---

## 6. Rutas principales

| Método | Ruta | Controlador | Nombre | Acceso |
|---|---|---|---|---|
| GET | `/` | HomeController@index | `home` | Público |
| GET | `/catalogo/buscar?q=&categoria=` | HomeController@buscar | `catalogo.buscar` | Público |
| GET/POST/PUT/DELETE | `/carrito` | CarritoController | `carrito.*` | Público (sesión) |
| POST | `/login`, `/logout` | LoginController | `login`, `logout` | Público |
| POST | `/cliente/registro` | RegisterClienteController | `cliente.register` | Público |
| GET | `/mi-cuenta/pedidos` | PedidoController@index | `cliente.mis-pedidos` | `auth` |
| GET | `/mi-cuenta/pedidos/{pedido}` | PedidoController@show | `cliente.pedido.show` | `auth` (solo su pedido) |
| GET/POST | `/checkout`, `/pedidos` | PedidoController | `pedidos.checkout`, `pedidos.store` | `auth` |
| GET | `/admin/dashboard` | HomeController@dashboardAdmin | `admin.dashboard` | `role:Administrador,Gerente,Subgerente,Contador,Almacenero,Vendedor` |
| resource | `/admin/productos` + PATCH toggle | ProductoController | `admin.productos.*` | `role:...` (personal) |
| GET/POST/PUT/DELETE | `/admin/categorias` + toggle | CategoriaController | `admin.categorias.*` | `role:...` |
| GET/POST | `/admin/inventario` (movimiento/ajustar/transferir) | InventarioController | `admin.inventario.*` | `role:...` |
| GET/PATCH | `/admin/pedidos`, `/admin/pedidos/{pedido}/estado` | PedidoAdminController | `admin.pedidos.*` | `role:...` (transiciones filtradas) |
| GET/POST | `/admin/ventas*` | VentaController | `admin.ventas.*` | `role:...` |
| GET/PUT | `/admin/clientes*` | ClienteController | `admin.clientes.*` | `role:...` |
| resource | `/admin/usuarios` + toggle | UsuarioController | `admin.usuarios.*` | `role:Administrador,Gerente` |

---

## 7. Flujo de pedido (PA3 § Flujo Comercial)

```
Cliente: Catálogo -> Carrito (sesión) -> Checkout (auth)
  -> Pedido PENDIENTE + HistorialPedido inicial
     (transacción, precio/stock revalidados vs BD con lockForUpdate)
  -> /mi-cuenta/pedidos (stepper + lista) -> /mi-cuenta/pedidos/{id} (detalle + timeline + venta)

Personal — todo 100% web, por rol, vía PedidoWorkflowService:
  Pendiente  -> Confirmado  (Vendedor, Administrador, Gerente, Subgerente)
  Confirmado -> En Almacén (Almacenero, Administrador, Gerente, Subgerente)
  En Almacén -> Enviado    (Almacenero, Vendedor, Administrador, Gerente, Subgerente)
  Enviado    -> Entregado  (Vendedor, Contador, Administrador, Gerente, Subgerente)
  *          -> Cancelado  (Vendedor, Administrador, Gerente, Subgerente)
  Terminales: Entregado, Cancelado (ya no avanzan)

Historial: historial_pedido (estado_anterior/nuevo + usuario_id + observación + timestamps)
           visible para cliente (timeline) y admin (timeline + stepper).
Venta: VentaController@store descuenta stock_por_almacen (lockForUpdate) + crea
       movimientos_inventario (SALIDA) + avanza a Entregado si el rol lo permite.
```

Reglas clave: revalidación anti-trampa en `PedidoController@store` y `VentaController@store`; `vendedor_id` solo se setea en transición a Confirmado.

---

## 8. Seguridad RBAC (PA3 § Módulo de Seguridad)

- **Auth**: `config/auth.php` usa `App\Models\Usuario` (tabla `usuarios`) + `BCRYPT_ROUNDS=12` (`.env`) + `casts ['password' => 'hashed']` + `Hash::make/check`.
- **Middleware**: `bootstrap/app.php` alias `role => RoleMiddleware`. Grupo `prefix admin` exige `role:Administrador,Gerente,Subgerente,Contador,Almacenero,Vendedor`; subgrupo `admin/usuarios` exige `role:Administrador,Gerente`. Cliente → `403` en `/admin/*`.
- **Helpers** en `Usuario`: `esAdmin()`, `esGerente()`, `esSubgerente()`, `esContador()`, `esAlmacenero()`, `esVendedor()`, `esCliente()`, `esPersonal()`.
- **Otros**: `@csrf` en todos los forms, validación estricta, paginación con límites, `APP_DEBUG=false` en prod.

---

## 9. Auditoría / Trazabilidad (PA3 § Auditoría — ISO 25010)

- **Observer pasivo**: `AuditObserver` (`created/updated/deleted`) registrado en `AppServiceProvider::boot()` sobre `Producto`, `Pedido`, `Venta` → `bitacora_cambios` (`usuario_id, tabla_afectada, accion, descripcion, datos_previos JSON, datos_nuevos JSON, fecha`).
- **Historial de pedido**: `historial_pedido` para trazabilidad del flujo por estados (quién movió, de dónde a dónde, observación).

---

## 10. Contratos y Servicios

- **Services** (arrays + inyección opcional de repositorios mockeables): `CatalogoService`, `PedidoService`, `InventarioService`, `VentaService`, `SeleccionProductoService`, etc.
- **Controllers** usan Eloquent directo para operaciones transaccionales.
- **Orquestador de estados**: `PedidoWorkflowService` centraliza `TRANSITIONS` + `ROLE_FOR_TARGET` + `puedeTransitar()` / `estadosPermitidos()` / `transitar()`.

---

## 11. Calidad — ISO/IEC 25010 (PA3)

| Pilar | Cómo lo cumple CCOLCAS |
|---|---|
| Adecuación funcional | BD normalizada, transacciones con `lockForUpdate`, stepper/timeline fieles, 168 tests |
| Seguridad | Hash BCRYPT 12, RBAC, `@csrf`, validación, auditoría |
| Usabilidad | Tailwind responsive, CSS por área, flujos cortos, seguimiento visible |
| Mantenibilidad | MVC desacoplado, Services/Contracts, factories, Git versionado |

Ver `docs/03-base-de-datos.md`, `docs/04-manual-usuario.md` y Entregable Final para diagramas (casos de uso, clases, navegación, secuencias) y capturas con fecha/hora.
