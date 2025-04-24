<?php
include("db.php");

if (isset($_POST['save_venta'])) {
    $fechaVenta = $_POST['fechaVenta'];
    $productos = $_POST['productoId'];
    $cantidades = $_POST['cantidadVenta'];
    $vendedorId = $_POST['vendedorId'];

    foreach ($productos as $index => $productoId) {
        $cantidadVenta = $cantidades[$index];

        // Verificar si hay suficiente cantidad en inventario
        $query_stock = "SELECT cantidad, precioVenta FROM Productos WHERE idProducto = $productoId";
        $result_stock = mysqli_query($conn, $query_stock);
        
        if (!$result_stock) {
            die("Query Failed: " . mysqli_error($conn));
        }

        $row_stock = mysqli_fetch_assoc($result_stock);
        $cantidadDisponible = $row_stock['cantidad'];
        $precioVenta = $row_stock['precioVenta'];

        if ($cantidadVenta > $cantidadDisponible) {
            // Si la cantidad a vender excede la disponible, no permitir la operación
            $_SESSION['message'] = "Error: No hay suficiente stock para el producto ID $productoId. Cantidad disponible: $cantidadDisponible.";
            $_SESSION['message_type'] = 'danger';

            // Redirigir sin procesar el resto de las ventas
            header("Location: dashboard.php");
            exit;
        }

        // Calcular precio total de la venta
        $precioVentaTotal = $precioVenta * $cantidadVenta;

        // Insertar la nueva entrada en la tabla ventas
        $query = "INSERT INTO ventas (fechaVenta, productoId, cantidadVenta, precioVentaTotal, vendedorId) 
                  VALUES ('$fechaVenta', '$productoId', '$cantidadVenta', '$precioVentaTotal', '$vendedorId')";
        $result = mysqli_query($conn, $query);

        if (!$result) {
            die("Query Failed: " . mysqli_error($conn));
        }

        // Actualizar la cantidad del producto en la tabla Productos
        $query_update = "UPDATE Productos SET cantidad = cantidad - $cantidadVenta WHERE idProducto = '$productoId'";
        $result_update = mysqli_query($conn, $query_update);

        if (!$result_update) {
            die("Query Failed: " . mysqli_error($conn));
        }
    }

    $_SESSION['message'] = 'Ventas registradas y cantidades actualizadas correctamente';
    $_SESSION['message_type'] = 'success';

    header("Location: dashboard.php");
}
?>

