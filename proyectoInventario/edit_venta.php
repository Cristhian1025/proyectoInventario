<?php
/**
 * edit_venta.php
 *
 * Página para editar una venta existente en la base de datos.
 * 
 * Funcionalidades principales:
 * - Obtiene los datos actuales de una venta mediante su ID.
 * - Permite editar la información de la venta (producto, cantidad, vendedor, etc.).
 * - Recalcula el total de la venta según el precio del producto y la nueva cantidad.
 * - Ajusta automáticamente el inventario de productos (resta y suma las cantidades).
 * - Actualiza los datos en la base de datos mediante sentencias preparadas.
 * 
 * Nota: No se modificó la lógica del código original, solo se añadieron comentarios descriptivos.
 */

include("db.php");

// --- BLOQUE 1: Obtener información de la venta seleccionada ---
if (isset($_GET['id'])) {
    $idVenta = $_GET['id'];

    // Consulta para obtener los datos actuales de la venta
    $query = "SELECT * FROM ventas WHERE idVenta = $idVenta";
    $result = mysqli_query($conn, $query);

    // Si la venta existe, se almacenan sus datos para mostrarlos en el formulario
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);
        $fechaVenta = $row['fechaVenta'];
        $productoId = $row['productoId'];
        $cantidadVenta = $row['cantidadVenta'];
        $precioVentaTotal = $row['precioVentaTotal'];
        $vendedorId = $row['vendedorId'];
    }
}

// --- BLOQUE 2: Actualizar la venta (al enviar el formulario) ---
if (isset($_POST['update'])) {
    $idVenta = $_GET['id'];
    $fechaVenta = $_POST['fechaVenta'];
    $nuevoProductoId = $_POST['productoId'];
    $nuevaCantidadVenta = $_POST['cantidadVenta'];

    // Obtener el precio unitario del producto seleccionado
    $query = "SELECT precioVenta FROM productos WHERE idProducto = $productoId";
    $result_precio = mysqli_query($conn, $query);

    if (!$result_precio) {
        die("Query Failed: " . mysqli_error($conn));
    }

    // Calcular el nuevo precio total según la cantidad
    $row_precio = mysqli_fetch_assoc($result_precio);
    $precioVenta = $row_precio['precioVenta'];
    $nuevoPrecioVentaTotal = $precioVenta * $nuevaCantidadVenta;

    $nuevoVendedorId = $_POST['vendedorId'];

    // --- Ajuste de inventario ---
    // 1. Recuperar la cantidad anterior y el producto anterior
    $query = "SELECT cantidadVenta, productoId FROM ventas WHERE idVenta = $idVenta";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    $cantidadAnterior = $row['cantidadVenta'];
    $productoIdAnterior = $row['productoId'];

    // 2. Reponer la cantidad anterior al inventario del producto previo
    $query = "UPDATE Productos SET cantidad = cantidad + $cantidadAnterior WHERE idProducto = $productoIdAnterior";
    mysqli_query($conn, $query);

    // 3. Descontar la nueva cantidad al producto seleccionado
    $query = "UPDATE Productos SET cantidad = cantidad - $nuevaCantidadVenta WHERE idProducto = $nuevoProductoId";
    mysqli_query($conn, $query);

    // --- Actualizar los datos de la venta ---
    $sql = "UPDATE ventas 
            SET fechaVenta = ?, productoId = ?, cantidadVenta = ?, precioVentaTotal = ?, vendedorId = ? 
            WHERE idVenta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siidii", $fechaVenta, $nuevoProductoId, $nuevaCantidadVenta, $nuevoPrecioVentaTotal, $nuevoVendedorId, $idVenta);

    if ($stmt->execute()) {
        // Mensaje de confirmación
        $_SESSION['message'] = 'Venta actualizada correctamente';
        $_SESSION['message_type'] = 'success';
    } else {
        echo "Error:<hr> " . $sql . "<hr>" . $conn->error;
    }

    // Redirigir al panel principal
    header("Location: dashboard.php");
}
?>

<?php include("includes/header.php") ?>

<!-- BLOQUE 3: Formulario de edición -->
<div class="container mt-5">
    <h2 class="mb-4">Editar Venta</h2>
    <form action="edit_venta.php?id=<?php echo $_GET['id']; ?>" method="POST">

        <!-- Fecha de venta -->
        <div class="form-group">
            <label for="fechaVenta">Fecha de Venta</label>
            <input type="date" class="form-control" id="fechaVenta" name="fechaVenta" value="<?php echo $fechaVenta; ?>" required>
        </div>

        <!-- Producto -->
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

        <!-- Cantidad -->
        <div class="form-group">
            <label for="cantidadVenta">Cantidad Vendida</label>
            <input type="number" class="form-control" id="cantidadVenta" name="cantidadVenta" value="<?php echo $cantidadVenta; ?>" required>
        </div>
        
        <!-- Vendedor -->
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
