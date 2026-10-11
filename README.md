# CCOLCA'S — Sistema Web de Gestión de Inventario, Almacén y Ventas

> **Distribuidora de Productos de Limpieza — Cusco | Sede Cusco 2026**
> **Stack:** Laravel 12 · PHP 8.2 · MySQL `ccolcasdb` (`BCRYPT_ROUNDS=12`) / SQLite `:memory:` en tests · Vite 7 · Tailwind 4 · Blade · Eloquent
> **Repositorio:** https://github.com/75957473-design/Colcas.git
> **Entregable Final:** `docs/IngWebEntregabes/EntregableFinal_Suble_Lima_Denilson_Rivaldo.docx` (lenguaje natural, 20 capítulos + anexos, 32 cajitas 📸 para capturas con fecha/hora)

Solución web interactiva que centraliza lo que antes estaba en cuadernos, Excels y WhatsApp: catálogo online, carrito, pedidos con seguimiento por rol, inventario multialmacén, precios calculados y ventas con comprobantes — con RBAC, auditoría y pruebas.

---

## Producto

- **Inventario multialmacén:** stock por `stock_por_almacen`, ingresos/salidas, ajustes y **transferencias origen→destino** (2 movimientos atómicos).
- **Precios:** `precio_venta = (costo_compra + costo_flete) * (1 + margen%)` — lo calcula el modelo y `admin.js` lo sugiere en vivo.
- **Comercial 100% web:** cliente confirma en `/checkout` y ve su avance en `/mi-cuenta/pedidos` (stepper + timeline); personal avanza el pedido por rol en `/admin/pedidos` (`PedidoWorkflowService`).
- **Ventas:** Boleta/Factura/Nota de Venta, descuenta stock con `lockForUpdate` y avanza a Entregado.
- **Trazabilidad:** `historial_pedido` por pedido + `bitacora_cambios` (Observer con `datos_previos/nuevos` JSON).

**Alcance v1 (4 semanas):** 8 módulos A–H operativos. Fuera de alcance: pagos con tarjeta/pasarelas, contabilidad completa y facturación electrónica (a futuro).

---

## Metodología / Calidad / Estrategia

- **OOHDM:** Conceptual (19 tablas) → Navegacional (público vs admin por rol) → Interfaz Abstracta ADV (Blade + Tailwind, CSS/JS por área) → Implementación MVC Laravel 12 + XAMPP.
- **Incremental:** 4 incrementos (infra → persistencia/seguridad → catálogo/almacén → pedidos/ventas/auditoría) desde 04/09/2026.
- **ISO/IEC 25010:** adecuación funcional, seguridad, usabilidad, mantenibilidad.
- **Evaluación tripartita:** heurística Nielsen (10) + pruebas funcionales (stock, precios por rol, descuento venta) + SUS (8–12 usuarios, umbral 68).
- **Seguridad:** DHARMA (híbrido), MADEJA (5 pilares), UCI RNFS 1–37, OWASP Top 10, INCIBE Básico/Avanzado.

---

## Requisitos

PHP 8.2+ (pdo_mysql, mbstring, openssl, fileinfo, gd) · Composer 2 · Node 18+ · MySQL 8 (XAMPP) · Git

---

## Instalación (Windows + XAMPP)

```bash
cd C:\xampp\htdocs
git clone https://github.com/75957473-design/Colcas.git Ccolcas
cd Ccolcas
composer install
npm install
copy .env.example .env
php artisan key:generate
# Crear BD ccolcasdb en http://localhost/phpmyadmin  (utf8mb4_unicode_ci) o:
php -r "new PDO('mysql:host=127.0.0.1','root','')->exec('CREATE DATABASE ccolcasdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');"
php artisan migrate --force
php artisan db:seed --force
npm run build   # prod — genera public/build/manifest.json (65 módulos)
# npm run dev  # dev — hot reload
php artisan serve  # http://127.0.0.1:8000  — o http://localhost/Ccolcas/public por Apache
```

`.env` clave: `APP_URL=http://localhost` `DB_CONNECTION=mysql` `DB_HOST=127.0.0.1` `DB_DATABASE=ccolcasdb` `BCRYPT_ROUNDS=12`
Tests usan `sqlite :memory:` automático (`phpunit.xml` + `BCRYPT_ROUNDS=4`). Ver `docs/01-instalacion.md`.

---

## Módulos (A–H)

| Módulo | Qué hace |
|---|---|
| **Catálogo** (público `/`) | Búsqueda + filtro categoría, solo `activo`, `inicio.css` |
| **Carrito / Checkout / Mis Pedidos** (Cliente `auth`) | Carrito en sesión, checkout revalida precio/stock vs BD, stepper + timeline (`pedidos.css`) |
| **Productos / Categorías** (Personal) | CRUD + toggle, fórmula precio, `admin.css` |
| **Inventario** (Personal) | Stock por almacén, movimientos ENTRADA/SALIDA, ajuste directo, **transferencia multialmacén** |
| **Pedidos** (workflow por rol) | `PedidoWorkflowService`: Pendiente→Confirmado (Vendedor)→En Almacén (Almacenero)→Enviado→Entregado (Contador/Vendedor) + Cancelado + `historial_pedido` |
| **Ventas** | Comprobantes, descuenta `stock_por_almacen` (`lockForUpdate`) |
| **Clientes** | DNI/RUC, dirección entrega, persona recepción |
| **Usuarios/Roles** (Admin/Gerente) | 7 roles, `RoleMiddleware` alias `role` |

---

## Arquitectura (resumen)

**MVC + capas:** `app/Models/*` (19) · `app/Http/Controllers/*` (13: Home, Carrito, Pedido, Auth×2, Admin×7) · `resources/views/*` (Blade) · `app/Observers/AuditObserver` → `bitacora_cambios` · `app/Services/*` (12 incl. `PedidoWorkflowService`) · `app/Contracts/*` (7) · `resources/css/*` (5) + `resources/js/*` (4) vía Vite 9 entradas · `routes/web.php` (6 públicas + 4 cliente + 22+ admin).

**RBAC:** `config/auth.php` model `Usuario` (tabla `usuarios`) + `BCRYPT_ROUNDS=12` + `casts hashed` · `bootstrap/app.php` alias `role` · `RoleMiddleware` · grupo `admin` exige 6 roles personal; `admin/usuarios` solo Administrador/Gerente (Cliente → 403).

**BD:** 5 migraciones (19 tablas + auxiliares) · 7 roles · 6 estados · `historial_pedido` + `bitacora_cambios` (`datos_previos/nuevos` JSON). Ver `docs/03-base-de-datos.md`.

Diagrama ER opcional: `php artisan generate:erd docs/diagrama-er.png --format=png` (requiere Graphviz).

---

## Comandos

```bash
php artisan migrate:fresh --seed
php artisan migrate:status
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=EstadosPedidoSeeder
php artisan test                    # 168 passed (335 assertions) — Unit 12 + Feature 3
php artisan test --filter=CatalogoFeature
php artisan route:list --path=admin
php artisan generate:erd --help
npm run dev
npm run build
```

---

## Documentación

- `docs/01-instalacion.md` — instalación paso a paso, verificación y errores comunes
- `docs/02-arquitectura.md` — stack, OOHDM, MVC, rutas, flujo por rol, RBAC, auditoría, calidad
- `docs/03-base-de-datos.md` — conexión, ER, 19 tablas, seeds, factories, relaciones
- `docs/04-manual-usuario.md` — catálogo, carrito, login/registro, checkout, mis pedidos, panel admin por rol (en lenguaje sencillo)
- `docs/IngWebEntregabes/` — ContenidoEntregableFinal y **EntregableFinal_Suble_Lima_Denilson_Rivaldo.docx**

---

## Licencia

MIT (Laravel)
