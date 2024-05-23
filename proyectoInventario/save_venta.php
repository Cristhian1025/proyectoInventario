<?php
include("db.php");

if (isset($_POST['save_venta'])) {
    
    $fechaVenta = $_POST['fechaVenta'];
    $productoId = $_POST['productoId'];
    $cantidadVenta = $_POST['cantidadVenta'];
    //...... pendiente

    $query = "SELECT precioVenta FROM productos where idProducto= $productoId";

    $result_precio = mysqli_query($conn, $query);
    if (!$result_precio) {
        die("Query Failed: " . mysqli_error($conn));
    }
    $row_precio = mysqli_fetch_assoc($result_precio);
    $precioVenta = $row_precio['precioVenta'];
    $precioVentaTotal = $precioVenta * $cantidadVenta;



    $vendedorId = $_POST['vendedorId'];

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

    $_SESSION['message'] = 'Venta registrada y cantidad actualizada correctamente';
    $_SESSION['message_type'] = 'success';

    header("Location: dashboard.php");
}
?>