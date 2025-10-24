<?php
/**
 * save_venta.php
 * 
 * Este archivo se encarga de procesar y guardar una nueva venta en la base de datos.
 * Incluye la inserción del registro principal en la tabla `ventas`, los detalles en 
 * la tabla `detalle_venta`, y la actualización del stock de productos.
 * 
 * Autor: [Tu nombre o equipo]
 * Fecha: [Coloca la fecha actual]
 */

ini_set('display_errors', 1); // Muestra errores en pantalla (útil para depuración)
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); // Reporta todos los errores posibles

include("db.php"); // Conexión a la base de datos

// Verifica si el formulario fue enviado correctamente
if (isset($_POST['save_venta'])) {

    // Inicia sesión solo si aún no hay una activa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Captura los datos del formulario
    $fechaVenta = $_POST['fechaVenta'];
    $vendedorId = $_POST['vendedorId'];
    $totalVenta = $_POST['precioVentaTotal'];
    $productosIds = $_POST['productoId']; // Arreglo con los IDs de los productos vendidos
    $cantidades = $_POST['cantidadVenta']; // Arreglo con las cantidades vendidas

    // Inicia una transacción para asegurar que todas las operaciones se completen correctamente
    mysqli_begin_transaction($conn);

    try {
        /**
         * 1️⃣ Insertar la venta principal en la tabla `ventas`
         */
        $query_venta = "INSERT INTO ventas (fechaVenta, vendedorId, totalVenta) VALUES (?, ?, ?)";
        $stmt_venta = mysqli_prepare($conn, $query_venta);
        mysqli_stmt_bind_param($stmt_venta, "sid", $fechaVenta, $vendedorId, $totalVenta);

        // Ejecuta la inserción y verifica errores
        if (!mysqli_stmt_execute($stmt_venta)) {
            throw new Exception("Error al guardar la venta principal: " . mysqli_stmt_error($stmt_venta));
        }

        // Obtiene el ID autogenerado de la venta recién insertada
        $ventaId = mysqli_insert_id($conn);

        /**
         * 2️⃣ Recorrer cada producto vendido e insertar su detalle
         */
        foreach ($productosIds as $index => $productoId) {
            $cantidad = $cantidades[$index];

            // Obtiene el precio actual y stock disponible del producto
            $query_prod_info = "SELECT precioVenta, cantidad FROM productos WHERE idProducto = ? FOR UPDATE";
            $stmt_prod_info = mysqli_prepare($conn, $query_prod_info);
            mysqli_stmt_bind_param($stmt_prod_info, "i", $productoId);
            mysqli_stmt_execute($stmt_prod_info);
            $result_prod_info = mysqli_stmt_get_result($stmt_prod_info);
            $prod_info = mysqli_fetch_assoc($result_prod_info);

            // Almacena el precio unitario y el stock disponible
            $precioUnitario = $prod_info['precioVenta'];
            $stockDisponible = $prod_info['cantidad'];

            // Verifica si hay suficiente stock para completar la venta
            if ($cantidad > $stockDisponible) {
                throw new Exception("No hay suficiente stock para el producto ID $productoId. Disponible: $stockDisponible, Solicitado: $cantidad");
            }

            // Inserta el detalle de la venta (producto, cantidad y precio)
            $query_detalle = "INSERT INTO detalle_venta (ventaId, productoId, cantidad, precioUnitario) VALUES (?, ?, ?, ?)";
            $stmt_detalle = mysqli_prepare($conn, $query_detalle);
            mysqli_stmt_bind_param($stmt_detalle, "iiid", $ventaId, $productoId, $cantidad, $precioUnitario);
            if (!mysqli_stmt_execute($stmt_detalle)) {
                throw new Exception("Error al guardar el detalle del producto: " . mysqli_stmt_error($stmt_detalle));
            }

            // Actualiza el stock restando la cantidad vendida
            $query_update_stock = "UPDATE productos SET cantidad = cantidad - ? WHERE idProducto = ?";
            $stmt_update_stock = mysqli_prepare($conn, $query_update_stock);
            mysqli_stmt_bind_param($stmt_update_stock, "ii", $cantidad, $productoId);
            if (!mysqli_stmt_execute($stmt_update_stock)) {
                throw new Exception("Error al actualizar el stock: " . mysqli_stmt_error($stmt_update_stock));
            }
        }

        /**
         * 3️⃣ Si todo salió bien, confirmar la transacción
         */
        mysqli_commit($conn);

        // Guarda mensaje de éxito en la sesión
        $_SESSION['message'] = 'Venta registrada correctamente con ID: ' . $ventaId;
        $_SESSION['message_type'] = 'success';

        // Redirige al listado de ventas
        header("Location: listado_ventas.php");

    } catch (Exception $e) {
        /**
         * Si ocurre un error en cualquier paso, se revierte todo (rollback)
         */
        mysqli_rollback($conn);
        $_SESSION['message'] = 'Error al registrar la venta: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        header("Location: ventas.php");
    }

} else {
    // Si el formulario no fue enviado, redirige a la página principal de ventas
    header("Location: ventas.php");
}
?>
