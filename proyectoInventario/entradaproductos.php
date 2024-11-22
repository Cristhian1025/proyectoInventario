<?php 
include("db.php");

// Obtener los productos
$query_productos = "SELECT idProducto, nombreProducto FROM Productos";
$result_productos = mysqli_query($conn, $query_productos);
$productos = mysqli_fetch_all($result_productos, MYSQLI_ASSOC);

// Obtener los proveedores
$query_proveedores = "SELECT idProveedor, nombreProveedor FROM Proveedores";
$result_proveedores = mysqli_query($conn, $query_proveedores);
$proveedores = mysqli_fetch_all($result_proveedores, MYSQLI_ASSOC);
?>

<?php include("includes/header.php") ?>

<div class="container mt-5">
    <h2 class="mb-4">Registrar Entrada de Productos</h2>
    <form action="save_entrada.php" method="POST">
        <div class="form-group">
            <label for="fechaEntrada">Fecha de Entrada</label>
            <input type="date" class="form-control" id="fechaEntrada" name="fechaEntrada" required>
        </div>
        <div class="form-group">
            <label for="productoId">Producto</label>
            <select class="form-control" id="productoId" name="productoId" required>
                <option value="">Selecciona un producto</option>
                <?php foreach ($productos as $producto): ?>
                    <option value="<?php echo $producto['idProducto']; ?>">
                        <?php echo $producto['nombreProducto']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="cantidadComprada">Cantidad Comprada</label>
            <input type="number" class="form-control" id="cantidadComprada" name="cantidadComprada" required>
        </div>
        <div class="form-group">
            <label for="precioCompraUnidad">Precio de Compra por Unidad</label>
            <input type="number" step="0.01" class="form-control" id="precioCompraUnidad" name="precioCompraUnidad" required>
        </div>
        <div class="form-group">
            <label for="proveedorId">Proveedor</label>
            <select class="form-control" id="proveedorId" name="proveedorId" required>
                <option value="">Selecciona un proveedor</option>
                <?php foreach ($proveedores as $proveedor): ?>
                    <option value="<?php echo $proveedor['idProveedor']; ?>">
                        <?php echo $proveedor['nombreProveedor']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="submit" class="btn btn-success btn-block mx-4 my-4" name="save_entrada" value="Registrar Entrada">
    </form>

</div>

<?php include("includes/footer.php") ?>