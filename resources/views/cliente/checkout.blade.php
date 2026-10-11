<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra | CCOLCAS</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/inicio.css', 'resources/css/pedidos.css'])
</head>

<body>

    <header class="top-header">
        <div class="header-container">
            <a href="{{ url('/') }}" class="logo">
                <i class="fa-solid fa-pump-soap"></i> CCOLCAS
            </a>
            <a href="{{ route('carrito.index') }}" style="color: #fff; text-decoration: none;">
                <i class="fa-solid fa-arrow-left"></i> Volver al Carrito
            </a>
        </div>
    </header>

    <div class="orders-container">
        <div class="orders-header">
            <h1 class="orders-title">
                <i class="fa-solid fa-credit-card"></i> Confirmar Pedido
            </h1>
        </div>

        <form action="{{ route('pedidos.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 350px; gap: 20px;">
                <!-- Formulario de Envío -->
                <div class="order-card" style="padding: 20px;">
                    <h3><i class="fa-solid fa-truck"></i> Datos de Envío y Comprobante</h3>
                    <hr style="margin: 15px 0; border: 0; border-top: 1px solid #e2e8f0;">

                    <div style="margin-bottom: 15px;">
                        <label for="direccion_envio" style="display: block; font-weight: 600; margin-bottom: 5px;">Dirección de Entrega *</label>
                        <textarea name="direccion_envio" id="direccion_envio" rows="3" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;" placeholder="Ej. Av. El Sol 123, Cusco">{{ old('direccion_envio', $cliente->direccion_entrega ?? '') }}</textarea>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="tipo_comprobante_solicitado" style="display: block; font-weight: 600; margin-bottom: 5px;">Tipo de Comprobante *</label>
                        <select name="tipo_comprobante_solicitado" id="tipo_comprobante_solicitado" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                            <option value="Boleta">Boleta</option>
                            <option value="Factura">Factura</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="observaciones" style="display: block; font-weight: 600; margin-bottom: 5px;">Notas / Observaciones de entrega (Opcional)</label>
                        <textarea name="observaciones" id="observaciones" rows="2" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;" placeholder="Ej. Entregar en portería, llamar antes de llegar."></textarea>
                    </div>
                </div>

                <!-- Resumen de Pedido -->
                <div class="order-card" style="padding: 20px; height: fit-content;">
                    <h3>Resumen del Pedido</h3>
                    <hr style="margin: 15px 0; border: 0; border-top: 1px solid #e2e8f0;">

                    @php $total = 0; @endphp
                    @foreach($carrito as $item)
                        @php $subtotal = $item['precio'] * $item['cantidad']; $total += $subtotal; @endphp
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem;">
                            <span>{{ $item['nombre'] }} x {{ $item['cantidad'] }}</span>
                            <strong>S/ {{ number_format($subtotal, 2) }}</strong>
                        </div>
                    @endforeach

                    <hr style="margin: 15px 0; border: 0; border-top: 1px solid #e2e8f0;">
                    
                    <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; margin-bottom: 20px;">
                        <span>Total a Pagar:</span>
                        <span style="color: #059669;">S/ {{ number_format($total, 2) }}</span>
                    </div>

                    <button type="submit" class="btn-back-store" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; border: none; cursor: pointer;">
                        <i class="fa-solid fa-check-circle"></i> Confirmar y Guardar Pedido
                    </button>
                </div>
            </div>
        </form>
    </div>

</body>
</html>