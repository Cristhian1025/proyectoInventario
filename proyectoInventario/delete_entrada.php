<?php
include("db.php");

if (isset($_GET['id'])) {
    $idEntrada = $_GET['id'];

    // Obtener la cantidad comprada y el producto ID antes de eliminar
    $query = "SELECT cantidadComprada, productoId FROM EntradaProductos WHERE idEntrada = $idEntrada";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    $cantidadComprada = $row['cantidadComprada'];
    $productoId = $row['productoId'];

    // Restar la cantidad comprada del producto
    $query = "UPDATE Productos SET cantidad = cantidad - $cantidadComprada WHERE idProducto = $productoId";
    mysqli_query($conn, $query);

    // Eliminar la entrada de producto
    $query = "DELETE FROM EntradaProductos WHERE idEntrada = $idEntrada";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query Failed.");
    }

    $_SESSION['message'] = 'Entrada eliminada correctamente';
    $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
}
?>