<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras | CCOLCAS</title>

    <!-- FontAwesome Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Carga modular de CSS y JS desde resources vía Vite -->
    @vite(['resources/css/inicio.css', 'resources/css/carrito.css', 'resources/js/carrito.js'])
</head>

<body>

    <!-- CABECERA SUPERIOR -->
    <header class="top-header">
        <div class="header-container">
            <a href="{{ url('/') }}" class="logo">
                <i class="fa-solid fa-pump-soap"></i> CCOLCAS
            </a>
            <a href="{{ url('/') }}" style="color: #fff; text-decoration: none; font-weight: 500;">
                <i class="fa-solid fa-arrow-left"></i> Seguir Comprando
            </a>
        </div>
    </header>

    <!-- CONTENEDOR PRINCIPAL DEL CARRITO -->
    <div class="cart-page-container">
        
        <!-- TABLA / LISTA DE PRODUCTOS -->
        <div class="cart-table-card">
            <h2><i class="fa-solid fa-cart-shopping"></i> Tu Carrito de Compras</h2>
            <br>

            @if(session('success'))
                <div class="alert-success-box">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif

            @if(count($carrito) > 0)
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach($carrito as $item)
                            @php $grandTotal += $item['subtotal']; @endphp
                            <!-- Data Attributes para que JS lea la información de los productos -->
                            <tr class="cart-item-row" 
                                data-nombre="{{ $item['nombre'] }}" 
                                data-cantidad="{{ $item['cantidad'] }}" 
                                data-subtotal="{{ number_format($item['subtotal'], 2) }}">
                                <td>
                                    <div class="cart-product-cell">
                                        <img src="{{ $item['url_imagen'] }}" class="cart-product-img" alt="{{ $item['nombre'] }}">
                                        <div>
                                            <strong>{{ $item['nombre'] }}</strong>
                                            <br>
                                            <small style="color: #6c757d;">{{ $item['categoria'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>S/ {{ number_format($item['precio'], 2) }}</td>
                                <td>
                                    <form action="{{ route('carrito.actualizar') }}" method="POST" style="display: flex; gap: 5px;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="producto_id" value="{{ $item['id'] }}">
                                        <input type="number" name="cantidad" value="{{ $item['cantidad'] }}" min="1" class="qty-input">
                                        <button type="submit" class="btn-update-qty" title="Actualizar cantidad">
                                            <i class="fa-solid fa-rotate"></i>
                                        </button>
                                    </form>
                                </td>
                                <td><strong>S/ {{ number_format($item['subtotal'], 2) }}</strong></td>
                                <td>
                                    <form action="{{ route('carrito.eliminar', $item['id']) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete" title="Eliminar del carrito">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="cart-empty-msg">
                    El carrito está vacío actualmente.<br><br>
                    <a href="{{ url('/') }}" style="font-weight: bold; text-decoration: underline;">
                        <i class="fa-solid fa-store"></i> Explora nuestro catálogo de productos
                    </a>
                </p>
            @endif
        </div>

        <!-- PANEL LATERAL DE RESUMEN Y CHECKOUT -->
        @php $subtotalGeneral = $grandTotal ?? 0; @endphp
        <div class="cart-summary-card" data-subtotal="{{ $subtotalGeneral }}">
            <div class="summary-title">Resumen del Pedido</div>

            <div class="summary-row">
                <span>Subtotal:</span>
                <span>S/ {{ number_format($subtotalGeneral, 2) }}</span>
            </div>

            <div class="delivery-option">
                <label><strong>Método de Entrega (Cusco):</strong></label>
                <label>
                    <input type="radio" name="tipo_entrega" value="tienda" checked> 
                    Recojo en Tienda (Gratis)
                </label>
                <label>
                    <input type="radio" name="tipo_entrega" value="delivery"> 
                    Envío a Domicilio (+S/ 7.00)
                </label>
            </div>

            <div class="summary-row summary-row-total">
                <span>Total Estimado:</span>
                <span id="txt-total-pagar" class="total-price">S/ {{ number_format($subtotalGeneral, 2) }}</span>
            </div>

            @if(count($carrito) > 0)
                <button type="button" id="btn-whatsapp" class="btn-whatsapp">
                    <i class="fa-brands fa-whatsapp" style="font-size: 22px;"></i> Confirmar Pedido por WhatsApp
                </button>
            @endif
        </div>

    </div>

</body>

</html>