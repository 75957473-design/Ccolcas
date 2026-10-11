// CCOLCAS Admin — interacciones del panel (sin frameworks)
document.addEventListener('DOMContentLoaded', () => {
    // Auto-calcular precio de venta en formularios de producto
    const costoCompra = document.getElementById('costo_compra');
    const costoFlete = document.getElementById('costo_flete');
    const margen = document.getElementById('margen_ganancia');
    const precioVenta = document.getElementById('precio_venta');

    function recalcularPrecio() {
        if (!costoCompra || !costoFlete || !margen || !precioVenta) return;
        const cc = parseFloat(costoCompra.value) || 0;
        const cf = parseFloat(costoFlete.value) || 0;
        const mg = parseFloat(margen.value) || 0;
        const pv = (cc + cf) * (1 + mg / 100);
        // Solo autocompletar si el usuario no lo editó manualmente
        if (precioVenta.dataset.manual !== '1') {
            precioVenta.value = pv.toFixed(2);
        }
    }

    [costoCompra, costoFlete, margen].forEach(el => {
        if (el) el.addEventListener('input', recalcularPrecio);
    });

    if (precioVenta) {
        precioVenta.addEventListener('input', () => {
            precioVenta.dataset.manual = '1';
        });
    }

    // Confirmación para acciones destructivas
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const msg = btn.getAttribute('data-confirm') || '¿Estás seguro?';
            if (!confirm(msg)) e.preventDefault();
        });
    });
});
