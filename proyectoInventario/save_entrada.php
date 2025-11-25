<?php
include("db.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['save_entrada'])) {
    $fechaEntrada = $_POST['fechaEntrada'];
    $productoId = $_POST['productoId'];
    $cantidadComprada = $_POST['cantidadComprada'];
    $precioCompraUnidad = $_POST['precioCompraUnidad'];
    $proveedorId = $_POST['proveedorId'];

    // Insertar la nueva entrada en la tabla EntradaProductos
    $query = "INSERT INTO EntradaProductos (fechaEntrada, productoId, cantidadComprada, precioCompraUnidad, proveedorId) 
              VALUES ('$fechaEntrada', '$productoId', '$cantidadComprada', '$precioCompraUnidad', '$proveedorId')";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query Failed: " . mysqli_error($conn));
    }

    // Actualizar la cantidad del producto en la tabla Productos
    $query_update = "UPDATE Productos SET cantidad = cantidad + $cantidadComprada WHERE idProducto = '$productoId'";
    $result_update = mysqli_query($conn, $query_update);

    if (!$result_update) {
        die("Query Failed: " . mysqli_error($conn));
    }

    $_SESSION['message'] = 'Entrada registrada y cantidad actualizada correctamente';
    $_SESSION['message_type'] = 'success';

    header("Location: dashboard.php");
}
?>