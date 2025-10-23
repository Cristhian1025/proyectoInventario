<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("db.php");

if (isset($_POST['save_venta'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $fechaVenta = $_POST['fechaVenta'];
    $vendedorId = $_POST['vendedorId'];
    $totalVenta = $_POST['precioVentaTotal'];
    $productosIds = $_POST['productoId'];
    $cantidades = $_POST['cantidadVenta'];
    $cedulaNit = !empty($_POST['cedulaNit']) ? $_POST['cedulaNit'] : NULL;
    $nombreCliente = !empty($_POST['nombreCliente']) ? $_POST['nombreCliente'] : NULL;

    // Iniciar transacción
    mysqli_begin_transaction($conn);

    try {
        // 1. Insertar la venta principal
        $query_venta = "INSERT INTO ventas (fechaVenta, vendedorId, totalVenta, cedulaNit, nombreCliente) VALUES (?, ?, ?, ?, ?)";
        $stmt_venta = mysqli_prepare($conn, $query_venta);
        mysqli_stmt_bind_param($stmt_venta, "sidss", $fechaVenta, $vendedorId, $totalVenta, $cedulaNit, $nombreCliente);
        if (!mysqli_stmt_execute($stmt_venta)) {
            throw new Exception("Error al guardar la venta principal: " . mysqli_stmt_error($stmt_venta));
        }
        $ventaId = mysqli_insert_id($conn);

        // 2. Recorrer productos y guardar detalles
        foreach ($productosIds as $index => $productoId) {
            $cantidad = $cantidades[$index];

            // Obtener precio del producto para seguridad y verificar stock
            $query_prod_info = "SELECT precioVenta, cantidad FROM productos WHERE idProducto = ? FOR UPDATE";
            $stmt_prod_info = mysqli_prepare($conn, $query_prod_info);
            mysqli_stmt_bind_param($stmt_prod_info, "i", $productoId);
            mysqli_stmt_execute($stmt_prod_info);
            $result_prod_info = mysqli_stmt_get_result($stmt_prod_info);
            $prod_info = mysqli_fetch_assoc($result_prod_info);
            $precioUnitario = $prod_info['precioVenta'];
            $stockDisponible = $prod_info['cantidad'];

            if ($cantidad > $stockDisponible) {
                throw new Exception("No hay suficiente stock para el producto ID $productoId. Disponible: $stockDisponible, Solicitado: $cantidad");
            }

            // Insertar en detalle_venta
            $query_detalle = "INSERT INTO detalle_venta (ventaId, productoId, cantidad, precioUnitario) VALUES (?, ?, ?, ?)";
            $stmt_detalle = mysqli_prepare($conn, $query_detalle);
            mysqli_stmt_bind_param($stmt_detalle, "iiid", $ventaId, $productoId, $cantidad, $precioUnitario);
            if (!mysqli_stmt_execute($stmt_detalle)) {
                throw new Exception("Error al guardar el detalle del producto: " . mysqli_stmt_error($stmt_detalle));
            }

            // Actualizar stock
            $query_update_stock = "UPDATE productos SET cantidad = cantidad - ? WHERE idProducto = ?";
            $stmt_update_stock = mysqli_prepare($conn, $query_update_stock);
            mysqli_stmt_bind_param($stmt_update_stock, "ii", $cantidad, $productoId);
            if (!mysqli_stmt_execute($stmt_update_stock)) {
                throw new Exception("Error al actualizar el stock: " . mysqli_stmt_error($stmt_update_stock));
            }
        }

        // Si todo fue bien, confirmar transacción
        mysqli_commit($conn);
        $_SESSION['message'] = 'Venta registrada correctamente con ID: ' . $ventaId;
        $_SESSION['message_type'] = 'success';
        header("Location: listado_ventas.php");

    } catch (Exception $e) {
        // Si algo falló, revertir todos los cambios
        mysqli_rollback($conn);
        $_SESSION['message'] = 'Error al registrar la venta: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        header("Location: ventas.php");
    }

} else {
    header("Location: ventas.php");
}
?>