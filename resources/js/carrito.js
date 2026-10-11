document.addEventListener('DOMContentLoaded', () => {
    const summaryCard = document.querySelector('.cart-summary-card');
    if (!summaryCard) return; // Si no estamos en la vista de carrito, no ejecutar

    // Obtener datos iniciales renderizados desde los data-attributes de HTML
    const baseSubtotal = parseFloat(summaryCard.dataset.subtotal || 0);
    const txtTotalPagar = document.getElementById('txt-total-pagar');
    const radioEntrega = document.querySelectorAll('input[name="tipo_entrega"]');
    const btnWhatsApp = document.getElementById('btn-whatsapp');

    // 1. Recalcular total dinámicamente según el método de envío
    function actualizarTotalMetodo() {
        const opcionSeleccionada = document.querySelector('input[name="tipo_entrega"]:checked');
        if (!opcionSeleccionada) return;

        const esDelivery = opcionSeleccionada.value === 'delivery';
        const costoDelivery = esDelivery ? 7.00 : 0.00;
        const total = baseSubtotal + costoDelivery;

        if (txtTotalPagar) {
            txtTotalPagar.innerText = 'S/ ' + total.toFixed(2);
        }
    }

    // Registrar eventos para el cambio de tipo de entrega
    radioEntrega.forEach(radio => {
        radio.addEventListener('change', actualizarTotalMetodo);
    });

    // 2. Construir la plantilla del mensaje y enviar a WhatsApp
    if (btnWhatsApp) {
        btnWhatsApp.addEventListener('click', () => {
            const telefonoWhatsApp = "51987654321"; // Teléfono oficial de CCOLCAS
            const opcionSeleccionada = document.querySelector('input[name="tipo_entrega"]:checked');
            const tipoEntrega = opcionSeleccionada ? opcionSeleccionada.value : 'tienda';
            const costoDelivery = tipoEntrega === 'delivery' ? 7.00 : 0.00;
            const total = baseSubtotal + costoDelivery;

            // Extraer items del carrito desde los data-attributes del HTML
            const items = document.querySelectorAll('.cart-item-row');

            let mensaje = "*NUEVO PEDIDO - CCOLCAS DISTRIBUIDORA*\n";
            mensaje += "==================================\n";

            items.forEach(item => {
                const nombre = item.dataset.nombre;
                const cantidad = item.dataset.cantidad;
                const subtotal = item.dataset.subtotal;
                mensaje += `• ${nombre} (x${cantidad}) - S/ ${subtotal}\n`;
            });

            mensaje += "==================================\n";
            mensaje += `*Subtotal:* S/ ${baseSubtotal.toFixed(2)}\n`;
            mensaje += `*Entrega:* ${tipoEntrega === 'delivery' ? 'Envío a domicilio en Cusco (+S/ 7.00)' : 'Recojo en tienda'}\n`;
            mensaje += `*TOTAL:* S/ ${total.toFixed(2)}\n\n`;
            mensaje += "Hola, desearía confirmar el stock y coordinar los detalles de pago/entrega.";

            const url = `https://wa.me/${telefonoWhatsApp}?text=${encodeURIComponent(mensaje)}`;
            window.open(url, '_blank');
        });
    }
});