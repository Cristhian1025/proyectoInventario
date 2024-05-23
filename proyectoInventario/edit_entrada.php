<?php
include("db.php");

if (isset($_GET['id'])) {
    $idEntrada = $_GET['id'];
    $query = "SELECT * FROM EntradaProductos WHERE idEntrada = $idEntrada";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);
        $fechaEntrada = $row['fechaEntrada'];
        $productoId = $row['productoId'];
        $cantidadComprada = $row['cantidadComprada'];
        $precioCompraUnidad = $row['precioCompraUnidad'];
        $proveedorId = $row['proveedorId'];
    }
}

if (isset($_POST['update'])) {
    $idEntrada = $_GET['id'];
    $fechaEntrada = $_POST['fechaEntrada'];
    $nuevoProductoId = $_POST['productoId'];
    $nuevaCantidadComprada = $_POST['cantidadComprada'];
    $precioCompraUnidad = $_POST['precioCompraUnidad'];
    $nuevoProveedorId = $_POST['proveedorId'];

    // Obtener la cantidad anterior del producto
    $query = "SELECT cantidadComprada, productoId FROM EntradaProductos WHERE idEntrada = $idEntrada";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    $cantidadAnterior = $row['cantidadComprada'];
    $productoIdAnterior = $row['productoId'];

    // Restar la cantidad anterior del producto anterior
    $query = "UPDATE Productos SET cantidad = cantidad - $cantidadAnterior WHERE idProducto = $productoIdAnterior";
    mysqli_query($conn, $query);

    // Sumar la nueva cantidad al nuevo producto
    $query = "UPDATE Productos SET cantidad = cantidad + $nuevaCantidadComprada WHERE idProducto = $nuevoProductoId";
    mysqli_query($conn, $query);

    // Actualizar la entrada de producto
    $query = "UPDATE EntradaProductos SET fechaEntrada = '$fechaEntrada', productoId = '$nuevoProductoId', cantidadComprada = '$nuevaCantidadComprada', precioCompraUnidad = '$precioCompraUnidad', proveedorId = '$nuevoProveedorId' WHERE idEntrada = $idEntrada";
    mysqli_query($conn, $query);

    $_SESSION['message'] = 'Entrada actualizada correctamente';
    $_SESSION['message_type'] = 'success';
    header("Location: dashboard.php");
}
?>

<?php include("includes/header.php") ?>

<div class="container mt-5">
    <h2 class="mb-4">Editar Entrada</h2>
    <form action="edit_entrada.php?id=<?php echo $_GET['id']; ?>" method="POST">
        <div class="form-group">
            <label for="fechaEntrada">Fecha de Entrada</label>
            <input type="date" class="form-control" id="fechaEntrada" name="fechaEntrada" value="<?php echo $fechaEntrada; ?>" required>
        </div>
        <div class="form-group">
            <label for="productoId">Producto</label>
            <select class="form-control" id="productoId" name="productoId" required>
                <?php
                $query = "SELECT idProducto, nombreProducto FROM Productos";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $selected = ($row['idProducto'] == $productoId) ? 'selected' : '';
                    echo "<option value='{$row['idProducto']}' $selected>{$row['nombreProducto']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="cantidadComprada">Cantidad Comprada</label>
            <input type="number" class="form-control" id="cantidadComprada" name="cantidadComprada" value="<?php echo $cantidadComprada; ?>" required>
        </div>
        <div class="form-group">
            <label for="precioCompraUnidad">Precio de Compra por Unidad</label>
            <input type="number" step="0.01" class="form-control" id="precioCompraUnidad" name="precioCompraUnidad" value="<?php echo $precioCompraUnidad; ?>" required>
        </div>
        <div class="form-group">
            <label for="proveedorId">Proveedor</label>
            <select class="form-control" id="proveedorId" name="proveedorId" required>
                <?php
                $query = "SELECT idProveedor, nombreProveedor FROM Proveedores";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $selected = ($row['idProveedor'] == $proveedorId) ? 'selected' : '';
                    echo "<option value='{$row['idProveedor']}' $selected>{$row['nombreProveedor']}</option>";
                }
                ?>
                
            </select>
        </div>
        <button type="submit" class="btn btn-primary" name="update">Actualizar</button>
    </form>
</div>

<?php include("includes/footer.php") ?>