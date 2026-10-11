# 04 — Manual de Usuario

> CCOLCAS — Sistema Web de Gestión de Inventario, Almacén y Ventas
> Laravel 12 · MySQL `ccolcasdb` · Sede Cusco 2026
> Repositorio: https://github.com/75957473-design/Colcas.git · Entregable Final: `docs/IngWebEntregabes/EntregableFinal_Suble_Lima_Denilson_Rivaldo.docx`

Este manual te explica, en lenguaje sencillo, cómo usar CCOLCAS según quién eres. Cada persona ve solo lo que le toca por su rol.

---

## 1. Cómo entrar

| Entorno | URL | Notas |
|---|---|---|
| Desarrollo | `http://127.0.0.1:8000` | `php artisan serve` |
| XAMPP Apache | `http://localhost/Ccolcas/public` | Proyecto en `C:\xampp\htdocs\Ccolcas` |

- Si ves `Vite manifest not found`, ejecuta `npm run build` y recarga.
- Si ves `403` en `/admin/*`, es porque entraste como Cliente o sin login.

---

## 2. Catálogo — La vitrina (`/`)

**¿Quién lo ve?** Todos, sin necesidad de login.

- **Arriba:** buscador por nombre/descripción/código + filtro por categoría + badge con cantidad del carrito.
- **Tarjetas:** foto (`images/products/*.jpg`), nombre, categoría, descripción, **precio S/**, **stock total** (suma de `stock_por_almacen`), botón **Añadir**.
- Solo se muestran productos con `activo = true`. Paginado.
- CSS: `resources/css/inicio.css` · JS: `resources/js/inicio.js` · Controlador: `HomeController@index` / `buscar`.

**Tip:** Busca "detergente" o filtra por "Limpieza" para encontrar rápido.

---

## 3. Carrito (`/carrito`)

**¿Quién lo ve?** Todos (vive en la sesión).

- Tabla con: foto, nombre, precio unitario, **cantidad editable** (PUT), subtotal, botón **Eliminar** (DELETE).
- Panel lateral con **resumen** y total.
- Valida que no pidas más de lo que hay (avisa "Stock insuficiente").
- CSS: `resources/css/carrito.css` · JS: `resources/js/carrito.js` · Controlador: `CarritoController`.

---

## 4. Registro y Login (modal en `/`)

Dos pestañas en la misma ventana:

| Pestaña | Qué pides | Qué pasa |
|---|---|---|
| **Ingresar** | Email + contraseña | Verifica con `Hash::check`. Si eres **Cliente** → `/`; si eres **Personal** (6 roles) → `/admin/dashboard`. |
| **Registrarse como Cliente** | Nombre, tipo/numero documento (DNI/RUC), teléfono, email, contraseña + confirmación | Crea `Usuario` (rol **Cliente**, password con `BCRYPT_ROUNDS=12`) + `Cliente` en **transacción**, te loguea automático y te lleva al catálogo. |

- Contraseña con hash seguro (`Usuario casts ['password'=>'hashed']`).
- Sin usuarios por defecto — el primer admin se crea por seeder o por `/admin/usuarios`.

---

## 5. Checkout (`/checkout`) — Confirmar tu pedido

**Requisitos:** estar logueado + carrito no vacío. Si no, te redirige.

1. Revisa tu **dirección de entrega** (viene pre-llenada de `cliente.direccion_entrega`, editable).
2. Elige **tipo de comprobante**: Boleta o Factura.
3. Escribe **observaciones** si quieres (ej: "Dejar en portería").
4. Mira el **resumen** y el **total**.
5. Click **Confirmar pedido**.

**Qué hace el sistema por debajo** (`PedidoController@store`):
- Vuelve a leer **precio y stock reales** de la BD con `lockForUpdate` (anti-trampa: aunque cambies el precio en el navegador, se recalcula).
- Si falta stock, te avisa y no crea el pedido.
- Crea `Pedido` (estado **Pendiente**) + `DetallePedidos` + `HistorialPedido` inicial en **transacción**.
- Vacía el carrito y te lleva a **Mis Pedidos**.

---

## 6. Mis Pedidos (`/mi-cuenta/pedidos`) y Detalle (`/mi-cuenta/pedidos/{id}`)

**¿Quién lo ve?** Solo el cliente dueño (filtrado por `cliente.usuario_id`). Requiere `auth`.

- **Lista:** cada pedido con número, fecha, **badge de estado con color**, **stepper** visual `Pendiente → Confirmado → En Almacén → Enviado → Entregado`, tabla de productos, total y link **Ver detalle y seguimiento**.
- **Detalle:** todo lo anterior + **línea de tiempo auditable** (quién movió el pedido, de qué estado a cuál, cuándo y con qué observación — viene de `historial_pedido`) + bloque de **venta asociada** si ya se facturó.
- CSS: `resources/css/pedidos.css` (`.stepper`, `.timeline`).

> El cliente confirma 100% por web y ve el avance en tiempo real, sin WhatsApp.

---

## 7. Panel Admin (`/admin/*`) — Solo personal

**¿Quién entra?** Cualquier rol **excepto Cliente**. Middleware `role:Administrador,Gerente,Subgerente,Contador,Almacenero,Vendedor` en `routes/web.php`. Cliente → `403`.

- **Layout:** `layouts/admin.blade.php` · CSS `resources/css/admin.css` · JS `resources/js/admin.js` (separados por área, PA3).
- **Dashboard** (`/admin/dashboard`): saludo personalizado, stats rápidas y menú lateral: Principal / Catálogo (Productos, Categorías) / Operaciones (Inventario, Pedidos, Ventas) / Personas (Clientes, Usuarios).

### 7.1 Productos (`/admin/productos`)

- Lista con **buscador** y filtros por **categoría** y **activo**, paginada.
- Acciones: **Crear**, **Editar**, **Ver**, **Activar/Desactivar** (toggle).
- Formulario: código, nombre, descripción, categoría, unidad, **costo_compra**, **costo_flete**, **margen_ganancia**, **precio_venta**, stock mínimo, imagen. Fórmula: `precio_venta = (costo_compra + costo_flete) * (1 + margen%)` — `admin.js` lo calcula en vivo, pero puedes ponerlo manual.
- Controlador: `Admin\ProductoController`.

### 7.2 Categorías (`/admin/categorias`)

- CRUD + **toggle activo/inactivo**. Controlador: `Admin\CategoriaController`.

### 7.3 Inventario (`/admin/inventario`) — Control de existencias PA3

- **Stock por almacén** con badges: OK / Crítico / Agotado.
- **Historial:** últimos 20 `movimientos_inventario`.
- **3 formularios:**
  1. **Registrar movimiento:** elige producto + almacén + tipo (ENTRADA/SALIDA) + cantidad + motivo → crea `movimientos_inventario` y actualiza `stock_por_almacen` con `lockForUpdate`.
  2. **Ajuste directo:** pones la **cantidad exacta** que debe haber (ej: tras conteo físico) → recalcula diferencia y genera movimiento.
  3. **Transferencia entre almacenes** (nuevo PA3): elige **origen** y **destino** (distintos) + producto + cantidad → en **transacción atómica** resta en origen, suma en destino, crea 2 movimientos: *Salida por Transferencia* + *Entrada por Transferencia*. Valida stock suficiente en origen.
- Controlador: `Admin\InventarioController` (`movimiento`, `ajustar`, `transferir`).

### 7.4 Pedidos Admin (`/admin/pedidos`)

- **Lista:** filtro por **estado**, buscador por cliente/número, paginada.
- **Detalle** (`/admin/pedidos/{pedido}`): stepper + timeline + **form "Actualizar estado"**:
  - Solo muestra los **estados que tu rol puede hacer** (filtrado por `PedidoWorkflowService::estadosPermitidos()`).
  - Campo **observación** opcional (ej: "Falta stock, se avisó al cliente").
  - Botón **Registrar venta** si el pedido lo permite.
- **Workflow por rol** (ver §8) — estados terminales **Entregado** y **Cancelado** ya no avanzan.
- Controlador: `Admin\PedidoAdminController`.

### 7.5 Ventas (`/admin/ventas`)

- **Lista** con buscador, **Crear desde pedido** (elige pedido en estado válido, tipo comprobante Boleta/Factura/Nota de Venta, número, método pago Efectivo/Transferencia/YapePlin, monto), y **Detalle** con datos del pedido.
- Al guardar: descuenta `stock_por_almacen` con `lockForUpdate` + crea `movimientos_inventario` (SALIDA por Venta) + avanza pedido a **Entregado** si el rol lo permite — todo en **transacción**.
- Controlador: `Admin\VentaController`.

### 7.6 Clientes (`/admin/clientes`)

- Lista, **Ver** y **Editar**: tipo/numero documento, nombre/razón social, teléfono, email, **dirección de entrega**, persona que recepciona.
- Controlador: `Admin\ClienteController`.

### 7.7 Usuarios y Roles (`/admin/usuarios`) — Solo jefes

- **Solo `Administrador` y `Gerente`** (`role:Administrador,Gerente`).
- CRUD (sin show/destroy), **toggle activo/inactivo**, asignación de **7 roles**.
- Controlador: `Admin\UsuarioController`.

---

## 8. Roles y permisos (PA3 RBAC)

| Rol | ¿Qué puede hacer en la web? |
|---|---|
| **Cliente** | Catálogo, carrito, checkout, **Mis Pedidos + seguimiento** · **Sin** `/admin` (403) |
| **Vendedor** | Pedidos: Confirmar/Cancelar, Enviar, Entregar · Ventas · Clientes · Productos/Categorías (lectura/CRUD según config) |
| **Almacenero** | **Inventario** (movimientos, ajustes, **transferencias**) · Pedidos: En Almacén, Enviado |
| **Contador** | Pedidos: Entregado · Ventas · Precios (costo/flete/margen) |
| **Subgerente** | Todo lo de Vendedor + Almacenero + Contador (según matriz) |
| **Gerente** | Todo lo anterior + **Usuarios y Roles** |
| **Administrador** | Acceso total |

**Matriz de workflow** (`PedidoWorkflowService::ROLE_FOR_TARGET`):

| Destino | Roles que pueden mover hacia ahí |
|---|---|
| **Confirmado** | Vendedor, Administrador, Gerente, Subgerente |
| **En Almacén** | Almacenero, Administrador, Gerente, Subgerente |
| **Enviado** | Almacenero, Vendedor, Administrador, Gerente, Subgerente |
| **Entregado** | Vendedor, Contador, Administrador, Gerente, Subgerente |
| **Cancelado** | Vendedor, Administrador, Gerente, Subgerente |

Transiciones válidas: `Pendiente → Confirmado/Cancelado → En Almacén → Enviado → Entregado`.

---

## 9. Consejos y solución rápida

| Problema | Qué hacer |
|---|---|
| No veo mis pedidos | ¿Estás logueado con el mismo usuario con que compraste? Ve a `/mi-cuenta/pedidos`. |
| No puedo entrar a `/admin` | Solo personal. Si eres Cliente verás 403. Pide a un Admin que te cambie el rol en `/admin/usuarios`. |
| El precio no cuadra | El precio se recalcula contra la BD al hacer checkout. Revisa costo/flete/margen en Productos. |
| Stock negativo o no descuenta | Las ventas descuentan al registrarse. Si fue pedido sin venta, el stock no baja aún. |
| Pantalla sin estilos | Ejecuta `npm run build` y recarga con Ctrl+F5. |
| BD vacía | `php artisan migrate:fresh --seed` (borra todo y recarga demo). |

¿Dudas? Revisa `docs/01-instalacion.md` (instalación), `docs/02-arquitectura.md` (cómo está construido) y `docs/03-base-de-datos.md` (tablas).
