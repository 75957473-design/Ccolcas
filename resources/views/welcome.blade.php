<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCOLCAS | Distribuidora de Productos de Limpieza</title>

    <!-- FontAwesome Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Carga desde resources/css/inicio.css con Vite -->
    @vite(['resources/css/inicio.css', 'resources/js/inicio.js'])
</head>

<body>

    <!-- BARRA SUPERIOR TIPO ALIEXPRESS -->
    <header class="top-header">
        <div class="header-container">
            <a href="{{ url('/') }}" class="logo">
                <i class="fa-solid fa-pump-soap"></i> CCOLCAS
            </a>

            <div class="search-box">
                <form action="{{ route('catalogo.buscar') }}" method="GET" class="search-form">
                    <input type="text" name="q" class="search-input" value="{{ $q ?? '' }}"
                        placeholder="Buscar detergentes, lejía, desinfectantes...">
                    <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>

            <div class="header-actions">
                @auth
                    <span style="color: #fff; font-weight: 600;"><i class="fa-regular fa-user"></i>
                        {{ Auth::user()->nombre_completo }}</span>
                    @if (Auth::user()->esCliente() || Auth::user()->cliente)
                        <a href="{{ route('cliente.mis-pedidos') }}" style="color:#fff; text-decoration:none;"><i
                                class="fa-solid fa-box"></i> Mis Pedidos</a>
                    @else
                        <a href="{{ route('admin.dashboard') }}" style="color:#fff; text-decoration:none;"><i
                                class="fa-solid fa-chart-line"></i> Panel</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit"
                            style="background:none; border:none; color:#fff; cursor:pointer; font-weight:bold;"><i
                                class="fa-solid fa-right-from-bracket"></i></button>
                    </form>
                @else
                    <button class="btn-auth-open" onclick="toggleModal(true, 'login')">
                        <i class="fa-regular fa-user"></i> Ingresar
                    </button>
                @endauth

                <a href="{{ route('carrito.index') }}" class="cart-icon-btn">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="cart-badge">{{ array_sum(array_column(session('carrito', []), 'cantidad')) ?: 0 }}</span>
                </a>
            </div>
        </div>
    </header>

    <!-- MENSAJES DE ALERTA GLOBAL -->
    @if (session('success'))
        <div
            style="background-color: #d4edda; color: #155724; padding: 12px; margin: 15px auto; max-width: 1200px; border-radius: 6px; text-align: center; border: 1px solid #c3e6cb;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <!-- BANNER DESTACADO -->
    <section class="hero">
        <h1>Distribuidora de Limpieza CCOLCAS</h1>
        <p>Ventas al por mayor y menor para hogares, empresas e instituciones en Cusco.</p>
    </section>

    <!-- GRID DE PRODUCTOS -->
    <main class="main-content">
        <div class="section-header">
            <i class="fa-solid fa-fire" style="color: #ff4747;"></i> Productos Destacados
        </div>

        <form action="{{ route('catalogo.buscar') }}" method="GET" class="category-filter">
            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria">
                <option value="">Todas las categorías</option>
                @foreach ($categorias ?? [] as $categoria)
                    <option value="{{ $categoria->slug }}" {{ ($categoriaSeleccionada ?? '') === $categoria->slug ? 'selected' : '' }}>
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>
            @if (!empty($q))
                <input type="hidden" name="q" value="{{ $q }}">
            @endif
            <button type="submit">Filtrar</button>
        </form>

        <div class="products-grid">
            @forelse($productos ?? [] as $producto)
                <div class="product-card">
                    <div class="product-img">
                        <!-- Carga dinámica usando el Accessor del Modelo Producto -->
                        <img src="{{ $producto->url_imagen }}" alt="{{ $producto->nombre }}">
                    </div>
                    <div class="product-details">
                        <span class="product-cat">{{ $producto->categoria->nombre ?? 'Limpieza' }}</span>
                        <h3 class="product-title">{{ $producto->nombre }}</h3>
                        @if ($producto->descripcion)
                            <p class="product-description">{{ $producto->descripcion }}</p>
                        @endif
                        <div class="product-price">S/ {{ number_format($producto->precio_venta, 2) }}</div>
                        <p class="product-stock">
                            @if ($producto->stockTotal() > 0)
                                Stock: {{ $producto->stockTotal() }} unidades
                            @else
                                Agotado
                            @endif
                        </p>

                        <!-- FORMULARIO DIRECTO PARA AÑADIR AL CARRITO -->
                        <form action="{{ route('carrito.agregar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                            <input type="hidden" name="cantidad" value="1">

                            <button type="submit" class="btn-cart-add">
                                <i class="fa-solid fa-cart-plus"></i> Añadir
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="catalog-empty">No hay productos disponibles por el momento.</p>
            @endforelse
        </div>
    </main>

    <!-- MODAL INICIAR SESIÓN / REGISTRO CLIENTES -->
    <div id="authModal" class="modal @if ($errors->any()) active @endif">
        <div class="modal-body">
            <button class="close-btn" onclick="toggleModal(false)">&times;</button>

            <div class="tabs">
                <button id="tab-login" class="tab active" onclick="switchPane('login')">Iniciar Sesión</button>
                <button id="tab-register" class="tab" onclick="switchPane('register')">Registrarse
                    (Cliente)</button>
            </div>

            <!-- MOSTRAR ERRORES EN EL MODAL -->
            @if ($errors->any())
                <div
                    style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px; font-size: 14px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- LOGIN -->
            <div id="pane-login" class="form-pane active">
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            placeholder="correo@ejemplo.com">
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn-submit">Ingresar</button>
                </form>
            </div>

            <!-- REGISTRO CLIENTE -->
            <div id="pane-register" class="form-pane">
                <form action="{{ route('cliente.register') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Nombre Completo / Razón Social</label>
                        <input type="text" name="nombre_completo" value="{{ old('nombre_completo') }}" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label>Tipo Doc.</label>
                            <select name="tipo_documento">
                                <option value="DNI" {{ old('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI
                                </option>
                                <option value="RUC" {{ old('tipo_documento') == 'RUC' ? 'selected' : '' }}>RUC
                                </option>
                            </select>
                        </div>
                        <div class="form-group" style="flex:2;">
                            <label>N° Documento</label>
                            <input type="text" name="numero_documento" value="{{ old('numero_documento') }}"
                                required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Teléfono / WhatsApp</label>
                        <input type="text" name="telefono" value="{{ old('telefono') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn-submit">Crear Cuenta de Cliente</button>
                </form>
            </div>
        </div>
    </div>

</html>
