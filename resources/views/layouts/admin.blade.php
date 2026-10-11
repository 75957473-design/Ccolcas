<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Admin') | CCOLCAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/admin.css'])
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__brand">
            <i class="fa-solid fa-pump-soap"></i> CCOLCAS
        </a>

        <nav class="admin-sidebar__nav">
            <div class="admin-sidebar__section">Principal</div>
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__link {{ request()->routeIs('admin.dashboard') ? 'admin-sidebar__link--active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>

            <div class="admin-sidebar__section">Catálogo</div>
            <a href="{{ route('admin.productos.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.productos.*') ? 'admin-sidebar__link--active' : '' }}">
                <i class="fa-solid fa-box"></i> Productos
            </a>
            <a href="{{ route('admin.categorias.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.categorias.*') ? 'admin-sidebar__link--active' : '' }}">
                <i class="fa-solid fa-tags"></i> Categorías
            </a>

            <div class="admin-sidebar__section">Operaciones</div>
            <a href="{{ route('admin.inventario.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.inventario.*') ? 'admin-sidebar__link--active' : '' }}">
                <i class="fa-solid fa-warehouse"></i> Inventario
            </a>
            <a href="{{ route('admin.pedidos.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.pedidos.*') ? 'admin-sidebar__link--active' : '' }}">
                <i class="fa-solid fa-receipt"></i> Pedidos
            </a>
            <a href="{{ route('admin.ventas.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.ventas.*') ? 'admin-sidebar__link--active' : '' }}">
                <i class="fa-solid fa-cash-register"></i> Ventas
            </a>

            <div class="admin-sidebar__section">Personas</div>
            <a href="{{ route('admin.clientes.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.clientes.*') ? 'admin-sidebar__link--active' : '' }}">
                <i class="fa-solid fa-users"></i> Clientes
            </a>
            <a href="{{ route('admin.usuarios.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.usuarios.*') ? 'admin-sidebar__link--active' : '' }}">
                <i class="fa-solid fa-user-gear"></i> Usuarios y Roles
            </a>
        </nav>

        <div class="admin-sidebar__footer">
            <div style="font-weight:700; color:#fff;">{{ Auth::user()->nombre_completo }}</div>
            <div>{{ Auth::user()->rol->nombre ?? '' }}</div>
            <form action="{{ route('logout') }}" method="POST" style="margin-top:10px;">
                @csrf
                <button type="submit" class="btn btn--outline btn--sm" style="width:100%; justify-content:center;">
                    <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <h1 class="admin-topbar__title">@yield('page_title', 'Panel')</h1>
            <div class="admin-topbar__actions">
                <a href="{{ route('home') }}" class="btn btn--ghost btn--sm"><i class="fa-solid fa-store"></i> Ver tienda</a>
            </div>
        </header>

        <main class="admin-content">
            @if(session('success'))
                <div class="alert alert--success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert--error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert--error">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
