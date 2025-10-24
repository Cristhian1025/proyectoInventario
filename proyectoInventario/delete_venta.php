<?php
/**
 * delete_venta.php
 *
 * Este script elimina una venta de la base de datos y restaura el stock 
 * de los productos involucrados en la misma. Utiliza transacciones 
 * para asegurar la integridad de los datos.
 *
 * @package Inventario
 * @version 1.1
 */

include("db.php"); // Conexión a la base de datos

// Verifica que el ID de la venta se haya enviado por la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idVenta = (int) $_GET['id'];

    // Asegura que la sesión esté iniciada antes de usar $_SESSION
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Inicia una transacción para mantener la consistencia de los datos
    mysqli_begin_transaction($conn);

    try {
        /**
         * 1️⃣ Obtener los productos y cantidades asociados a la venta
         *    Esto permitirá restaurar el stock correctamente.
         */
        $query_detalles = "SELECT productoId, cantidad FROM detalle_venta WHERE ventaId = ?";
        $stmt_detalles = mysqli_prepare($conn, $query_detalles);
        mysqli_stmt_bind_param($stmt_detalles, "i", $idVenta);
        mysqli_stmt_execute($stmt_detalles);
        $result_detalles = mysqli_stmt_get_result($stmt_detalles);
        $detalles_venta = mysqli_fetch_all($result_detalles, MYSQLI_ASSOC);

        /**
         * 2️⃣ Restaurar el stock de cada producto vendido
         *    Si no hay detalles, simplemente se elimina la venta.
         */
        if (!empty($detalles_venta)) {
            foreach ($detalles_venta as $detalle) {
                $productoId = (int) $detalle['productoId'];
                $cantidadDevolver = (int) $detalle['cantidad'];

                $query_update_stock = "UPDATE productos SET cantidad = cantidad + ? WHERE idProducto = ?";
                $stmt_update_stock = mysqli_prepare($conn, $query_update_stock);
                mysqli_stmt_bind_param($stmt_update_stock, "ii", $cantidadDevolver, $productoId);

                if (!mysqli_stmt_execute($stmt_update_stock)) {
                    throw new Exception("Error al devolver el stock del producto ID: $productoId");
                }
                mysqli_stmt_close($stmt_update_stock);
            }
        }

        /**
         * 3️⃣ Eliminar la venta principal
         *    Si la relación tiene ON DELETE CASCADE, los detalles se eliminan automáticamente.
         */
        $query_delete_venta = "DELETE FROM ventas WHERE idVenta = ?";
        $stmt_delete_venta = mysqli_prepare($conn, $query_delete_venta);
        mysqli_stmt_bind_param($stmt_delete_venta, "i", $idVenta);

        if (!mysqli_stmt_execute($stmt_delete_venta)) {
            throw new Exception("Error al eliminar la venta principal (ID: $idVenta).");
        }

        // Confirmar transacción si todo fue correcto
        mysqli_commit($conn);

        $_SESSION['message'] = '✅ Venta eliminada correctamente y stock restaurado.';
        $_SESSION['message_type'] = 'success';

    } catch (Exception $e) {
        // Si ocurre un error, revertir todos los cambios
        mysqli_rollback($conn);
        $_SESSION['message'] = '❌ Error al eliminar la venta: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';

        // Registrar el error en el log del servidor
        error_log("Error eliminando venta ID $idVenta: " . $e->getMessage());
    }

    // Redirigir de vuelta al listado de ventas
    header("Location: listado_ventas.php");
    exit();

} else {
    // Si el ID no es válido o no se envió
    die("⚠️ ID de venta no proporcionado o inválido.");
}
?>
