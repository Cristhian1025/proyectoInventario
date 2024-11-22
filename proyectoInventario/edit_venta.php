<?php
include("db.php");

if (isset($_GET['id'])) {
    $idVenta = $_GET['id'];
    $query = "SELECT * FROM ventas WHERE idVenta = $idVenta";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);
        $fechaVenta = $row['fechaVenta'];
        $productoId = $row['productoId'];
        $cantidadVenta = $row['cantidadVenta'];
        $precioVentaTotal = $row['precioVentaTotal'];
        $vendedorId = $row['vendedorId'];
    }
}

if (isset($_POST['update'])) {
    $idVenta = $_GET['id'];
    $fechaVenta = $_POST['fechaVenta'];
    $nuevoProductoId = $_POST['productoId'];
    $nuevaCantidadVenta = $_POST['cantidadVenta'];
    $nuevoPrecioVentaTotal = $_POST['precioVentaTotal'];
    $nuevoVendedorId = $_POST['vendedorId'];

    // Obtener la cantidad anterior del producto vendido
    $query = "SELECT cantidadVenta, productoId FROM ventas WHERE idVenta = $idVenta";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    $cantidadAnterior = $row['cantidadVenta'];
    $productoIdAnterior = $row['productoId'];

    // Restar la cantidad anterior del producto anterior
    $query = "UPDATE Productos SET cantidad = cantidad + $cantidadAnterior WHERE idProducto = $productoIdAnterior";
    mysqli_query($conn, $query);

    // Sumar la nueva cantidad al nuevo producto
    $query = "UPDATE Productos SET cantidad = cantidad - $nuevaCantidadVenta WHERE idProducto = $nuevoProductoId";
    mysqli_query($conn, $query);


    // Actualizar el registro de la venta
    
    //$queryA = "UPDATE ventas SET fechaVenta = '$fechaVenta', productoId = '$nuevoProductoId', cantidadVenta = '$nuevaCantidadVenta', precioVentaTotal = '$nuevoPrecioVentaTotal', vendedorId = '$nuevoVendedorId' WHERE idVenta = $idVenta";

    //echo "ERROR: $queryA";


    $sql = "UPDATE ventas SET fechaVenta = ?, productoId = ?, cantidadVenta = ?, precioVentaTotal = ?, vendedorId = ? WHERE idVenta = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiiii", $fechaVenta, $productoId, $nuevaCantidadVenta, $precioVentaTotal, $nuevoVendedorId, $idVenta);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Venta actualizada correctamente';
        $_SESSION['message_type'] = 'success';
        } else {
            echo "Error:<hr> " . $sql . "<hr>" . $conn->error;
        }


    //mysqli_query($conn, $queryA);

    $_SESSION['message'] = 'Venta actualizada correctamente';
    $_SESSION['message_type'] = 'success';
    header("Location: dashboard.php");
}
?>

<?php include("includes/header.php") ?>

<div class="container mt-5">
    <h2 class="mb-4">Editar Venta</h2>
    <form action="edit_venta.php?id=<?php echo $_GET['id']; ?>" method="POST">
        <div class="form-group">
            <label for="fechaVenta">Fecha de Venta</label>
            <input type="date" class="form-control" id="fechaVenta" name="fechaVenta" value="<?php echo $fechaVenta; ?>" required>
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
            <label for="cantidadVenta">Cantidad Vendida</label>
            <input type="number" class="form-control" id="cantidadVenta" name="cantidadVenta" value="<?php echo $cantidadVenta; ?>" required>
        </div>
        <div class="form-group">
            <label for="precioVentaTotal">Precio Total de Venta</label>
            <input type="number" step="0.01" class="form-control" id="precioVentaTotal" name="precioVentaTotal" value="<?php echo $precioVentaTotal; ?>" required>
        </div>
        <div class="form-group">
            <label for="vendedorId">Vendedor</label>
            <select class="form-control" id="vendedorId" name="vendedorId" required>
                <?php
                $query = "SELECT idUsuario, nombreCompleto FROM usuario";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $selected = ($row['idUsuario'] == $vendedorId) ? 'selected' : '';
                    echo "<option value='{$row['idUsuario']}' $selected>{$row['nombreCompleto']}</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mx-4 my-4" name="update">Actualizar</button>
    </form>
</div>

<?php include("includes/footer.php") ?>
