<?php
include("db.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $idVenta = $_GET['id'];

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Iniciar transacción
    mysqli_begin_transaction($conn);

    try {
        // 1. Obtener los detalles de la venta para saber qué productos y cantidades devolver
        $query_detalles = "SELECT productoId, cantidad FROM detalle_venta WHERE ventaId = ?";
        $stmt_detalles = mysqli_prepare($conn, $query_detalles);
        mysqli_stmt_bind_param($stmt_detalles, "i", $idVenta);
        mysqli_stmt_execute($stmt_detalles);
        $result_detalles = mysqli_stmt_get_result($stmt_detalles);
        $detalles_venta = mysqli_fetch_all($result_detalles, MYSQLI_ASSOC);

        if (empty($detalles_venta)) {
            // Si no hay detalles, puede ser una venta vacía o un error. Solo borramos la venta principal.
            // No se lanza excepción para permitir la limpieza de posibles ventas huérfanas.
        } else {
            // 2. Devolver cada producto al stock
            foreach ($detalles_venta as $detalle) {
                $productoId = $detalle['productoId'];
                $cantidadDevolver = $detalle['cantidad'];

                $query_update_stock = "UPDATE productos SET cantidad = cantidad + ? WHERE idProducto = ?";
                $stmt_update_stock = mysqli_prepare($conn, $query_update_stock);
                mysqli_stmt_bind_param($stmt_update_stock, "ii", $cantidadDevolver, $productoId);
                if (!mysqli_stmt_execute($stmt_update_stock)) {
                    throw new Exception("Error al devolver el stock del producto ID: " . $productoId);
                }
            }
        }

        // 3. Eliminar la venta de la tabla 'ventas'
        // La FK con ON DELETE CASCADE se encargará de borrar los registros en 'detalle_venta'
        $query_delete_venta = "DELETE FROM ventas WHERE idVenta = ?";
        $stmt_delete_venta = mysqli_prepare($conn, $query_delete_venta);
        mysqli_stmt_bind_param($stmt_delete_venta, "i", $idVenta);
        if (!mysqli_stmt_execute($stmt_delete_venta)) {
            throw new Exception("Error al eliminar la venta principal.");
        }

        // Si todo fue bien, confirmar la transacción
        mysqli_commit($conn);
        $_SESSION['message'] = 'Venta eliminada correctamente y stock restaurado.';
        $_SESSION['message_type'] = 'success';

    } catch (Exception $e) {
        // Si algo falló, revertir todos los cambios
        mysqli_rollback($conn);
        $_SESSION['message'] = 'Error al eliminar la venta: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }

    header("Location: listado_ventas.php");
    exit();

} else {
    die("ID de venta no proporcionado.");
}
?>