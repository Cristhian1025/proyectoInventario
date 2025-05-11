<?php
// Incluye el archivo de conexión a la base de datos
include("db.php");

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Obtener los productos ---
$productos = [];
// ***** CORRECCIÓN AQUÍ: Incluir precioVenta en la consulta *****
$query_productos = "SELECT idProducto, nombreProducto, precioVenta FROM Productos ORDER BY nombreProducto ASC";
$result_productos_query = mysqli_query($conn, $query_productos);

if ($result_productos_query) {
    $productos = mysqli_fetch_all($result_productos_query, MYSQLI_ASSOC);
} else {
    error_log("Error al obtener productos: " . mysqli_error($conn));
    $_SESSION['message'] = 'Error crítico: No se pudo cargar la lista de productos.';
    $_SESSION['message_type'] = 'danger';
}

// --- Obtener los usuarios (vendedores) ---
$usuarios = [];
$query_usuarios = "SELECT idUsuario, nombreCompleto FROM usuario ORDER BY nombreCompleto ASC";
$result_usuarios_query = mysqli_query($conn, $query_usuarios);

if ($result_usuarios_query) {
    $usuarios = mysqli_fetch_all($result_usuarios_query, MYSQLI_ASSOC);
} else {
    error_log("Error al obtener usuarios: " . mysqli_error($conn));
    // Considera si este error también debe ser crítico para el usuario
}

// Incluye el encabezado de la página
include("includes/header.php");
?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Registrar Nueva Venta</h2>

    <?php if (isset($_SESSION['message'])) : ?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type']); ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    ?>
    <?php endif; ?>

    <form action="save_venta.php" method="POST" id="form-registrar-venta">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="fechaVenta" class="form-label">Fecha de Venta <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="fechaVenta" name="fechaVenta" value="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="vendedorId" class="form-label">Vendedor <span class="text-danger">*</span></label>
                <select class="form-select" id="vendedorId" name="vendedorId" required>
                    <option value="">Selecciona un vendedor</option>
                    <?php if (!empty($usuarios)) : ?>
                        <?php foreach ($usuarios as $usuario) : ?>
                            <option value="<?php echo htmlspecialchars($usuario['idUsuario']); ?>">
                                <?php echo htmlspecialchars($usuario['nombreCompleto']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <option value="" disabled>No hay vendedores disponibles</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <hr>
        <h4 class="mb-3">Productos</h4>
        <div id="productos-container">
            <div class="producto-item row align-items-end mb-3 p-3 border rounded bg-light">
                <div class="col-md-5">
                    <label class="form-label">Producto <span class="text-danger">*</span></label>
                    <select class="form-select producto-select" name="productoId[]" required>
                        <option value="">Selecciona un producto</option>
                        <?php if (!empty($productos)) : ?>
                            <?php foreach ($productos as $producto) : ?>
                                <option value="<?php echo htmlspecialchars($producto['idProducto']); ?>" 
                                        data-precio="<?php echo htmlspecialchars($producto['precioVenta'] ?? '0'); ?>">
                                    <?php echo htmlspecialchars($producto['nombreProducto']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <option value="" disabled>No hay productos disponibles</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="number" class="form-control cantidad-venta" name="cantidadVenta[]" min="1" required placeholder="Ej: 1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Precio U.</label>
                    <input type="text" class="form-control precio-unitario bg-white" name="precioUnitario[]" placeholder="$0.00" readonly>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-product-btn" style="display:none;">Eliminar</button>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-primary mb-3" id="add-product-btn">
            <i class="fas fa-plus"></i> Agregar Otro Producto
        </button>
        
        <hr>
        <div class="row justify-content-end">
            <div class="col-md-4 text-end"> {/* Alineado a la derecha */}
                <h4>Total Venta: <span id="total-venta-display" class="fw-bold">$0.00</span></h4>
                <input type="hidden" name="precioVentaTotal" id="precioVentaTotalInput" value="0">
            </div>
        </div>

        <div class="d-grid gap-2 mt-4">
            <input type="submit" class="btn btn-success btn-lg" name="save_venta" value="Registrar Venta">
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productosContainer = document.getElementById('productos-container');
    const addProductBtn = document.getElementById('add-product-btn');
    
    // Pasamos los datos de productos a JavaScript. 
    const productosDataPHP = <?php echo json_encode($productos); ?> || [];
    const productosData = {};
    productosDataPHP.forEach(p => {
        productosData[p.idProducto] = p; // Mapear por idProducto para fácil acceso
    });

    function updateRemoveButtonsVisibility() {
        const items = productosContainer.querySelectorAll('.producto-item');
        items.forEach((item, index) => {
            const removeBtn = item.querySelector('.remove-product-btn');
            if (removeBtn) {
                removeBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
            }
        });
    }

    function calculateAndUpdateTotals() {
        let granTotal = 0;
        productosContainer.querySelectorAll('.producto-item').forEach(item => {
            const productoSelect = item.querySelector('.producto-select');
            const cantidadInput = item.querySelector('.cantidad-venta');
            const precioUnitarioInput = item.querySelector('.precio-unitario');
            
            const productoId = productoSelect.value;
            const cantidad = parseInt(cantidadInput.value) || 0;
            
            let precioUnitario = 0;

            if (productoId && productosData[productoId] && typeof productosData[productoId].precioVenta !== 'undefined') {
                precioUnitario = parseFloat(productosData[productoId].precioVenta);
                if (isNaN(precioUnitario)) {
                    precioUnitario = 0; // Si el precioVenta no es un número válido
                    precioUnitarioInput.value = 'Error Precio';
                } else {
                    precioUnitarioInput.value = '$' + precioUnitario.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            } else {
                precioUnitarioInput.value = (productoId) ? 'Precio ND' : '$0.00'; // Precio No Disponible o $0.00 si no hay producto
            }
            
            granTotal += cantidad * precioUnitario;
        });
        
        document.getElementById('total-venta-display').textContent = '$' + granTotal.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('precioVentaTotalInput').value = granTotal.toFixed(2);
    }

    function addProductItemEventListeners(item) {
        const productoSelect = item.querySelector('.producto-select');
        const cantidadInput = item.querySelector('.cantidad-venta');
        const removeBtn = item.querySelector('.remove-product-btn');

        productoSelect.addEventListener('change', calculateAndUpdateTotals);
        cantidadInput.addEventListener('input', calculateAndUpdateTotals);
        
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                item.remove();
                updateRemoveButtonsVisibility();
                calculateAndUpdateTotals();
            });
        }
    }

    addProductBtn.addEventListener('click', () => {
        const firstItem = productosContainer.querySelector('.producto-item');
        if (!firstItem) return;

        const newItem = firstItem.cloneNode(true);
        
        newItem.querySelector('select.producto-select').value = '';
        newItem.querySelector('input.cantidad-venta').value = '';
        newItem.querySelector('input.precio-unitario').value = '$0.00';
        
        addProductItemEventListeners(newItem); // Añadir listeners al nuevo item
        
        productosContainer.appendChild(newItem);
        updateRemoveButtonsVisibility();
        calculateAndUpdateTotals(); 
    });
    
    // Añadir listeners a los items de producto iniciales
    productosContainer.querySelectorAll('.producto-item').forEach(item => {
        addProductItemEventListeners(item);
    });

    // Inicializar visibilidad de botones y cálculos
    updateRemoveButtonsVisibility();
    calculateAndUpdateTotals();
});
</script>

<?php include("includes/footer.php") ?>
