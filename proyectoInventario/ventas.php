<?php 
include("db.php");

// Obtener los productos
$query_productos = "SELECT idProducto, nombreProducto FROM Productos";
$result_productos = mysqli_query($conn, $query_productos);
$productos = mysqli_fetch_all($result_productos, MYSQLI_ASSOC);

// Obtener los usuarios
$query_usuario = "SELECT idUsuario, nombreCompleto FROM usuario";
$result_usuario = mysqli_query($conn, $query_usuario);
$usuarios = mysqli_fetch_all($result_usuario, MYSQLI_ASSOC);
?>

<?php include("includes/header.php") ?>

<div class="container mt-5">
    <h2 class="mb-4">Registrar Venta</h2>
    <form action="save_venta.php" method="POST">
        <div class="form-group">
            <label for="fechaVenta">Fecha de Venta</label>
            <input type="date" class="form-control" id="fechaVenta" name="fechaVenta" required>
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
            <label for="cantidadVenta">Cantidad Vendida</label>
            <input type="number" class="form-control" id="cantidadVenta" name="cantidadVenta" required>
        </div>

        <!--   //No Necesario....
        <div class="form-group">
            <label for="precioCompraUnidad">Precio de Compra por Unidad</label>
            <input type="number" step="0.01" class="form-control" id="precioCompraUnidad" name="precioCompraUnidad" required>
        </div>

        -->

        <div class="form-group">
            <label for="usuarioId">Vendedor</label>
            <select class="form-control" id="vendedorId" name="vendedorId" required>
                <option value="">Selecciona un vendedor</option>

                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario['idUsuario']; ?>">
                        <?php echo $usuario['nombreCompleto']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="submit" class="btn btn-success btn-block" name="save_venta" value="Registrar Entrada">
    </form>
</div>

<?php include("includes/footer.php") ?>