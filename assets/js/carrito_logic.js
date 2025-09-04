// Función para cambiar la imagen principal del producto (solo relevante si tienes botones de cambio de imagen)
function cambiarImagen(nuevaImagenSrc, clickedButton = null) {
    const productoImagen = document.getElementById('producto-imagen');
    if (productoImagen) {
        productoImagen.src = nuevaImagenSrc;
        // Opcional: Para cambiar el estilo del botón activo (si tienes CSS para 'active-color-selector')
        // const buttons = document.querySelectorAll('.color-selector');
        // buttons.forEach(btn => btn.classList.remove('active-color-selector'));
        // if (clickedButton) {
        //     clickedButton.classList.add('active-color-selector');
        // }
    }
}

// --- FUNCIONES DEL CARRITO (USANDO sessionStorage) ---

// Agrega un producto al carrito
function agregarAlCarrito(button) {
    const productId = button.dataset.productId;
    const productName = button.dataset.productName;
    const productPrice = parseFloat(button.dataset.productPrice);
    const productImage = button.dataset.productImage; // Esta ya viene con la barra / al inicio

    let carrito = JSON.parse(sessionStorage.getItem('carrito')) || [];

    const productoExistente = carrito.find(item => item.id === productId);

    if (productoExistente) {
        productoExistente.cantidad++;
    } else {
        carrito.push({
            id: productId,
            nombre: productName,
            precio: productPrice,
            imagen: productImage, // La ruta de la imagen ya es absoluta
            cantidad: 1
        });
    }

    sessionStorage.setItem('carrito', JSON.stringify(carrito));
    
    actualizarContadorCarrito(); // Actualiza el número en el botón flotante
    alert(`"${productName}" ha sido agregado al carrito.`); // Mensaje de confirmación
    // No redirigimos aquí. El usuario decide ir al carrito haciendo clic en el botón flotante.
}

// Actualiza el contador numérico del carrito flotante
function actualizarContadorCarrito() {
    const contadorCarrito = document.getElementById('contador-carrito');
    if (contadorCarrito) {
        let carrito = JSON.parse(sessionStorage.getItem('carrito')) || [];
        let totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
        contadorCarrito.textContent = totalItems;
        contadorCarrito.style.display = totalItems > 0 ? 'inline-block' : 'none';
    }
}

// Renderiza los productos en la tabla del carrito (solo relevante en carrito.php)
function renderizarCarrito() {
    const itemsCarritoBody = document.getElementById('items-carrito');
    const totalCarritoElement = document.getElementById('total-carrito');
    let carrito = JSON.parse(sessionStorage.getItem('carrito')) || [];
    let totalGeneral = 0;

    // Asegurarse de que los elementos existen antes de intentar manipularlos
    if (!itemsCarritoBody || !totalCarritoElement) {
        // Si no se encuentran los elementos de la tabla, significa que no estamos en la página del carrito.
        // No hay necesidad de renderizar nada.
        return;
    }

    itemsCarritoBody.innerHTML = ''; // Limpiar el contenido actual de la tabla

    if (carrito.length === 0) {
        itemsCarritoBody.innerHTML = '<tr><td colspan="6">Tu carrito está vacío.</td></tr>';
        totalCarritoElement.textContent = '$0';
        actualizarContadorCarrito(); // Asegurar que el contador también se ponga a 0
        return;
    }

    carrito.forEach(item => {
        const subtotal = item.precio * item.cantidad;
        totalGeneral += subtotal;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td><img src="${item.imagen}" alt="${item.nombre}" width="80"></td>
            <td>${item.nombre}</td>
            <td>$${item.precio.toLocaleString('es-AR')}</td>
            <td>
                <input type="number" class="form-control text-center cantidad-input" 
                       value="${item.cantidad}" min="1" style="width: 70px;" 
                       data-product-id="${item.id}">
            </td>
            <td>$${subtotal.toLocaleString('es-AR')}</td>
            <td><button class="btn btn-sm btn-danger eliminar-producto" data-product-id="${item.id}"><i class="bi bi-trash"></i></button></td>
        `;
        itemsCarritoBody.appendChild(row);
    });

    totalCarritoElement.textContent = `$${totalGeneral.toLocaleString('es-AR')}`;
    actualizarContadorCarrito();

    // Añadir Event Listeners DESPUÉS de que se hayan creado los elementos
    document.querySelectorAll('.eliminar-producto').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            eliminarProductoDelCarrito(productId);
        });
    });

    document.querySelectorAll('.cantidad-input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const newQuantity = parseInt(this.value);
            if (newQuantity > 0) {
                actualizarCantidadProducto(productId, newQuantity);
            } else {
                // Si la cantidad es 0 o menos, eliminar el producto
                eliminarProductoDelCarrito(productId);
            }
        });
    });
}

// Elimina un producto del carrito
function eliminarProductoDelCarrito(productId) {
    let carrito = JSON.parse(sessionStorage.getItem('carrito')) || [];
    carrito = carrito.filter(item => item.id !== productId);
    sessionStorage.setItem('carrito', JSON.stringify(carrito));
    renderizarCarrito(); // Vuelve a renderizar para actualizar la vista
}

// Actualiza la cantidad de un producto en el carrito
function actualizarCantidadProducto(productId, newQuantity) {
    let carrito = JSON.parse(sessionStorage.getItem('carrito')) || [];
    const producto = carrito.find(item => item.id === productId);
    if (producto) {
        producto.cantidad = newQuantity;
        sessionStorage.setItem('carrito', JSON.stringify(carrito));
        renderizarCarrito(); // Vuelve a renderizar para actualizar la vista y totales
    }
}

// Inicializar el carrito y el contador al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    // Ejecutar renderizarCarrito solo si los elementos de la tabla existen (es decir, estamos en carrito.php)
    if (document.getElementById('items-carrito')) {
        renderizarCarrito();
    }
    // Siempre actualizar el contador flotante, ya que este elemento puede estar en varias páginas
    actualizarContadorCarrito(); 
});



