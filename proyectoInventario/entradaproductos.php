<?php
/**
 * entradaproductos.php
 *
 * Página para gestionar y registrar las entradas de productos al inventario.
 * 
 * Funcionalidad:
 * - Carga la lista de productos y proveedores desde la base de datos.
 * - Muestra un formulario para registrar una nueva entrada de producto.
 * - Envía los datos a `save_entrada.php` para ser guardados.
 * 
 * Notas:
 * - Se usan sentencias preparadas para mayor seguridad y eficiencia.
 * - Incluye validación en el lado del cliente mediante Bootstrap.
 */

// --- Conexión a la base de datos ---
require_once("db.php");

// --- BLOQUE 1: Obtener productos disponibles ---
// Consulta preparada para obtener los productos registrados en la base de datos.
$query_productos = "SELECT idProducto, nombreProducto FROM Productos";
$stmt_productos = mysqli_prepare($conn, $query_productos);
mysqli_stmt_execute($stmt_productos);
$result_productos = mysqli_stmt_get_result($stmt_productos);
$productos = mysqli_fetch_all($result_productos, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_productos);

// --- BLOQUE 2: Obtener proveedores registrados ---
// Consulta preparada para obtener los proveedores disponibles.
$query_proveedores = "SELECT idProveedor, nombreProveedor FROM Proveedores";
$stmt_proveedores = mysqli_prepare($conn, $query_proveedores);
mysqli_stmt_execute($stmt_proveedores);
$result_proveedores = mysqli_stmt_get_result($stmt_proveedores);
$proveedores = mysqli_fetch_all($result_proveedores, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_proveedores);

// --- BLOQUE 3: Cargar encabezado HTML ---
require_once("includes/header.php");
?>

<!-- BLOQUE 4: Formulario principal de registro de entrada -->
<div class="container mt-5">
    <h2 class="mb-4">Registrar Entrada de Productos</h2>

    <!-- 
        Formulario para registrar una entrada de producto.
        Se envía mediante método POST a "save_entrada.php".
        Incluye validación con Bootstrap (clase 'needs-validation').
    -->
    <form action="save_entrada.php" method="POST" class="needs-validation" novalidate>

        <!-- Campo: Fecha de entrada -->
        <div class="form-group">
            <label for="fechaEntrada">Fecha de Entrada:</label>
            <input type="date" class="form-control" id="fechaEntrada" name="fechaEntrada" value="" required>
            <div class="invalid-feedback">Por favor, ingrese la fecha de entrada.</div>
        </div>

        <!-- Campo: Producto -->
        <div class="form-group">
            <label for="productoId">Producto:</label>
            <select class="form-control" id="productoId" name="productoId" required>
                <option value="">Seleccione un producto</option>
                <?php foreach ($productos as $producto): ?>
                    <option value="<?php echo $producto['idProducto']; ?>">
                        <?php echo $producto['nombreProducto']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Por favor, seleccione un producto.</div>
        </div>

        <!-- Campo: Cantidad comprada -->
        <div class="form-group">
            <label for="cantidadComprada">Cantidad Comprada:</label>
            <input type="number" class="form-control" id="cantidadComprada" name="cantidadComprada" value="" required min="1">
            <div class="invalid-feedback">Por favor, ingrese la cantidad comprada.</div>
            <small class="form-text text-muted">Ingrese un número mayor que cero.</small>
        </div>

        <!-- Campo: Precio de compra por unidad -->
        <div class="form-group">
            <label for="precioCompraUnidad">Precio de Compra por Unidad:</label>
            <input type="number" step="0.01" class="form-control" id="precioCompraUnidad" name="precioCompraUnidad"
                   value="" required min="0.01">
            <div class="invalid-feedback">Por favor, ingrese el precio de compra por unidad.</div>
            <small class="form-text text-muted">Ingrese un número mayor que cero.</small>
        </div>

        <!-- Campo: Proveedor -->
        <div class="form-group">
            <label for="proveedorId">Proveedor:</label>
            <select class="form-control" id="proveedorId" name="proveedorId" required>
                <option value="">Seleccione un proveedor</option>
                <?php foreach ($proveedores as $proveedor): ?>
                    <option value="<?php echo $proveedor['idProveedor']; ?>">
                        <?php echo $proveedor['nombreProveedor']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Por favor, seleccione un proveedor.</div>
        </div>

        <!-- Botón para registrar -->
        <input type="submit" class="btn btn-success btn-block mx-4 my-4" name="save_entrada" value="Registrar Entrada">
    </form>
</div>

<?php
// --- BLOQUE 5: Incluir pie de página ---
require_once("includes/footer.php");
?>

<!-- BLOQUE 6: Validación del formulario con JavaScript -->
<script>
    // Script de validación de formularios (Bootstrap 4)
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
