// Manejo del Modal de Autenticación
window.toggleModal = function(show, tab = 'login') {
    const modal = document.getElementById('authModal');
    if (!modal) return;
    
    if (show) {
        modal.classList.add('active');
        switchPane(tab);
    } else {
        modal.classList.remove('active');
    }
};

window.switchPane = function(tab) {
    const paneLogin = document.getElementById('pane-login');
    const paneRegister = document.getElementById('pane-register');
    const tabLogin = document.getElementById('tab-login');
    const tabRegister = document.getElementById('tab-register');

    if (paneLogin) paneLogin.classList.toggle('active', tab === 'login');
    if (paneRegister) paneRegister.classList.toggle('active', tab === 'register');
    if (tabLogin) tabLogin.classList.toggle('active', tab === 'login');
    if (tabRegister) tabRegister.classList.toggle('active', tab === 'register');
};

// Agregar al Carrito mediante Fetch AJAX
window.agregarAlCarrito = function(productoId, urlAgregar) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
        console.error("Token CSRF no encontrado.");
        return;
    }

    fetch(urlAgregar, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({
            producto_id: productoId,
            cantidad: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.querySelector('.cart-badge');
            if (badge) badge.innerText = data.cart_count || data.total_items;

            alert("¡Producto añadido al carrito correctamente!");
        }
    })
    .catch(error => console.error("Error al agregar al carrito:", error));
};