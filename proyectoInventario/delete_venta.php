<?php
include("db.php");

if (isset($_GET['id'])) {
    $idVenta = $_GET['id'];

    // Obtener la cantidad vendida y el producto ID antes de eliminar
    $query = "SELECT cantidadVenta, productoId FROM ventas WHERE idVenta = $idVenta";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);
        $cantidadVenta = $row['cantidadVenta'];
        $productoId = $row['productoId'];

        // Revertir la cantidad vendida al producto
        $query = "UPDATE Productos SET cantidad = cantidad + $cantidadVenta WHERE idProducto = $productoId";
        mysqli_query($conn, $query);

        // Eliminar la venta
        $query = "DELETE FROM ventas WHERE idVenta = $idVenta";
        $result = mysqli_query($conn, $query);

        if (!$result) {
            
            die("Query Failed: " . mysqli_error($conn));
        }

        $_SESSION['message'] = 'Venta eliminada correctamente';
        $_SESSION['message_type'] = 'danger';
        header("Location: dashboard.php");
    } else {
        die("Venta no encontrada.");
    }
}
?>