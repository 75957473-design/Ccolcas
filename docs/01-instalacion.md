# 01 — Instalación

> CCOLCAS — Sistema Web de Gestión de Inventario, Almacén y Ventas
> Laravel 12 · PHP 8.2 · MySQL `ccolcasdb` · Vite 7 · Tailwind 4
> Repositorio: https://github.com/75957473-design/Colcas.git

## 1. Requisitos

| Software | Versión mínima | Notas |
|---|---|---|
| PHP | 8.2 | Con extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd` |
| Composer | 2.x | Gestor de dependencias PHP |
| Node.js + npm | 18+ | Para compilar Vite/Tailwind |
| MySQL | 8.0 | Vía XAMPP (Apache + MySQL) en Windows |
| Git | cualquiera | Para clonar y versionar |
| XAMPP | 8.2 | Recomendado en Windows para Apache + phpMyAdmin |

## 2. Instalación paso a paso (Windows + XAMPP)

```bash
# 1. Clonar
cd C:\xampp\htdocs
git clone https://github.com/75957473-design/Colcas.git Ccolcas
cd Ccolcas

# 2. Dependencias PHP y JS
composer install
npm install

# 3. Entorno
copy .env.example .env
php artisan key:generate

# 4. Base de datos — enciende Apache y MySQL en XAMPP primero
# Opción A: phpMyAdmin http://localhost/phpmyadmin -> Crear BD "ccolcasdb" utf8mb4_unicode_ci
# Opción B: por comando:
php -r "new PDO('mysql:host=127.0.0.1','root','')->exec('CREATE DATABASE ccolcasdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');"

php artisan migrate --force
php artisan db:seed --force
# Seeders: RolesSeeder (7 roles) -> EstadosPedidoSeeder (6 estados) -> CatalogoSeeder (almacén + categorías + productos demo)

# 5. Assets (elige uno)
npm run build   # producción — genera public/build/manifest.json
npm run dev     # desarrollo — hot reload con Vite

# 6. Servir
php artisan serve
# Abre http://127.0.0.1:8000
# O por Apache: http://localhost/Ccolcas/public  (si el proyecto está en htdocs/Ccolcas)
```

> `composer setup` hace los pasos 2-4 y 5 en un solo comando.

## 3. Variables `.env` clave

```
APP_NAME=CCOLCAS
APP_ENV=local
APP_URL=http://localhost
BCRYPT_ROUNDS=12
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ccolcasdb
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
```

- `DB_DATABASE=ccolcasdb` — nombre exigido por PA1/PA3 (no `ccolcas`).
- `BCRYPT_ROUNDS=12` — fortaleza de hash (PA3 § Seguridad).
- Tests no tocan `.env`: `phpunit.xml` fuerza `DB_CONNECTION=sqlite` + `DB_DATABASE=:memory:` y `BCRYPT_ROUNDS=4` para velocidad.

## 4. Verificación

```bash
php artisan migrate:status          # 5 migraciones en "Ran"
php artisan db:seed --force         # "Database seeding completed"
php artisan test                    # 168 passed (335 assertions)
npm run build                       # 65 módulos transformados, manifest.json generado
php artisan generate:erd docs/diagrama-er.png --format=png   # ER opcional (requiere Graphviz)
```

Abre en el navegador:
- `http://127.0.0.1:8000` — catálogo público
- `http://127.0.0.1:8000/admin/dashboard` — panel (requiere login con rol != Cliente)
- `http://localhost/phpmyadmin` — verifica BD `ccolcasdb` con 19 tablas + auxiliares

## 5. Comandos útiles

```bash
php artisan migrate:fresh --seed         # reset total BD + seeds
php artisan migrate:status               # estado de migraciones
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=EstadosPedidoSeeder
php artisan db:seed --class=CatalogoSeeder
php artisan test                         # 168 tests (Unit 12 suites + Feature 3 suites)
php artisan test --filter=CatalogoFeature
php artisan route:list --path=admin      # 22+ rutas admin
php artisan generate:erd --help          # diagrama ER
npm run dev                              # Vite watch
npm run build                            # build prod
```

## 6. Errores comunes

| Error | Causa | Solución |
|---|---|---|
| `SQLSTATE[HY000] [1049] Unknown database 'ccolcasdb'` | No creaste la BD | Ejecuta el `CREATE DATABASE ccolcasdb` de arriba y luego `php artisan migrate --force` |
| `Vite manifest not found at public/build/manifest.json` | No compilaste assets | Ejecuta `npm run build` y recarga |
| `403 Forbidden` en `/admin/*` | Entraste como Cliente o sin login | Inicia sesión con un usuario Personal (Administrador/Gerente/Vendedor/Almacenero/Contador/Subgerente) |
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL apagado | Enciende MySQL en el panel de XAMPP |
| Tests fallan con `no such table: productos` | Cache de config | Ejecuta `php artisan config:clear` y luego `php artisan test` |
