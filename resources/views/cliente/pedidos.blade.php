<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos | CCOLCAS</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Carga de CSS vía Vite -->
    @vite(['resources/css/inicio.css', 'resources/css/pedidos.css'])
</head>

<body>

    <header class="top-header">
        <div class="header-container">
            <a href="{{ url('/') }}" class="logo">
                <i class="fa-solid fa-pump-soap"></i> CCOLCAS
            </a>
            <a href="{{ url('/') }}" style="color: #fff; text-decoration: none;">
                <i class="fa-solid fa-store"></i> Volver a la Tienda
            </a>
        </div>
    </header>

    <div class="orders-container">
        
        <div class="orders-header">
            <h1 class="orders-title">
                <i class="fa-solid fa-box-open"></i> Mis Pedidos
            </h1>
            <a href="{{ url('/') }}" class="btn-back-store">
                <i class="fa-solid fa-cart-plus"></i> Realizar Nuevo Pedido
            </a>
        </div>

        @if(isset($pedidos) && $pedidos->count() > 0)
            @foreach($pedidos as $pedido)
                <div class="order-card">
                    <div class="order-card-header">
                        <div>
                            <span class="order-code">Pedido #{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</span>
                            <span class="order-date"> • {{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y h:i A') }}</span>
                        </div>
                        <span class="badge-status status-{{ Str::slug($pedido->estado->nombre ?? 'pendiente') }}">
                            {{ $pedido->estado->nombre ?? 'Pendiente' }}
                        </span>
                    </div>

                    @php
                        $flujo = ['Pendiente','Confirmado','En Almacén','Enviado','Entregado'];
                        $idx = array_search($pedido->estado->nombre ?? 'Pendiente', $flujo);
                    @endphp
                    @if($pedido->estado->nombre !== 'Cancelado')
                        <div class="stepper" style="padding:12px 20px 0;">
                            @foreach($flujo as $i => $nom)
                                <div class="stepper__step {{ $i < $idx ? 'stepper__step--done' : ($i === $idx ? 'stepper__step--current' : '') }}">{{ $nom }}</div>
                            @endforeach
                        </div>
                    @else
                        <div style="padding:10px 20px;"><span class="badge" style="background:#fee2e2; color:#991b1b;">Cancelado</span></div>
                    @endif

                    <div class="order-card-body">
                        <table class="order-details-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio Unit.</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedido->detalles as $detalle)
                                    <tr>
                                        <td><strong>{{ $detalle->producto->nombre ?? 'Producto no disponible' }}</strong></td>
                                        <td>S/ {{ number_format($detalle->precio_unitario, 2) }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                        <td>S/ {{ number_format($detalle->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="order-card-footer">
                        <div class="order-info-meta">
                            <strong>Comprobante:</strong> {{ $pedido->tipo_comprobante_solicitado }}<br>
                            <strong>Dirección de Envío:</strong> {{ $pedido->direccion_envio }}
                        </div>
                        <div class="order-total">
                            Monto Total: S/ {{ number_format($pedido->monto_total, 2) }}
                        </div>
                    </div>
                    <div style="padding:12px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                        <a href="{{ route('cliente.pedido.show', $pedido) }}" style="font-weight:700; text-decoration:none; color:#0f172a;"><i class="fa-solid fa-eye"></i> Ver detalle y seguimiento</a>
                        <span style="font-size:12px; color:#64748b;"><i class="fa-solid fa-circle-info"></i> Confirmado por ti vía web el {{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty-orders">
                <i class="fa-solid fa-receipt"></i>
                <h2>Aún no has realizado ningún pedido</h2>
                <p>Explora nuestro catálogo de productos de limpieza y realiza tu primera compra.</p>
                <a href="{{ url('/') }}" class="btn-back-store">Explorar Catálogo</a>
            </div>
        @endif

    </div>

</body>
</html>