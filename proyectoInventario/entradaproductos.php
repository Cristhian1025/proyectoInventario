<?php
/**
 * entradaproductos.php
 *
 * Descripción: Formulario para registrar la entrada de productos al inventario.
 */

// Incluye el archivo de configuración de la base de datos.
require_once("db.php");

// Obtener los productos de la base de datos.  Se usa una sola consulta preparada para mayor eficiencia.
$query_productos = "SELECT idProducto, nombreProducto FROM Productos";
$stmt_productos = mysqli_prepare($conn, $query_productos);
mysqli_stmt_execute($stmt_productos);
$result_productos = mysqli_stmt_get_result($stmt_productos);
$productos = mysqli_fetch_all($result_productos, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_productos);

// Obtener los proveedores de la base de datos, similar a la consulta de productos.
$query_proveedores = "SELECT idProveedor, nombreProveedor FROM Proveedores";
$stmt_proveedores = mysqli_prepare($conn, $query_proveedores);
mysqli_stmt_execute($stmt_proveedores);
$result_proveedores = mysqli_stmt_get_result($stmt_proveedores);
$proveedores = mysqli_fetch_all($result_proveedores, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_proveedores);

// Incluye el encabezado de la página.
require_once("includes/header.php");
?>

<div class="container mt-5">
    <div class="card card-glass">
        <div class="card-header">
            <h2 class="mb-0">Registrar Entrada de Productos</h2>
        </div>
        <div class="card-body">
            <form action="save_entrada.php" method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
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
                    </div>
                    <div class="col-md-6">
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
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cantidadComprada">Cantidad Comprada:</label>
                            <input type="number" class="form-control" id="cantidadComprada" name="cantidadComprada" value="" required
                                   min="1" max="989999">
                            <div class="invalid-feedback">Por favor, ingrese la cantidad comprada.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="precioCompraUnidad">Precio de Compra por Unidad:</label>
                            <input type="number" step="0.01" class="form-control" id="precioCompraUnidad" name="precioCompraUnidad"
                                   value="" required min="0.01" max="9999.99">
                            <div class="invalid-feedback">Por favor, ingrese el precio de compra por unidad.</div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fechaEntrada">Fecha de Entrada:</label>
                            <input type="date" class="form-control" id="fechaEntrada" name="fechaEntrada" value="" required>
                            <div class="invalid-feedback">Por favor, ingrese la fecha de entrada.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <input type="submit" class="btn btn-success" name="save_entrada" value="Registrar Entrada">
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Incluye el pie de página.
require_once("includes/footer.php");
?>

<script>
    // Inicializa la validación de Bootstrap.
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
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
