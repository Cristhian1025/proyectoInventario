<?php
/**
 * save_entrada.php
 *
 * Descripción:
 * Este script se encarga de procesar el formulario de registro de nuevas entradas de productos.
 * Inserta los datos en la tabla `EntradaProductos` y actualiza el stock en la tabla `Productos`.
 */

// Incluye el archivo de conexión a la base de datos
include("db.php");

// Verifica si el formulario fue enviado con el botón "save_entrada"
if (isset($_POST['save_entrada'])) {

    // Captura los datos enviados desde el formulario
    $fechaEntrada = $_POST['fechaEntrada'];             // Fecha en la que se realiza la entrada del producto
    $productoId = $_POST['productoId'];                 // ID del producto ingresado
    $cantidadComprada = $_POST['cantidadComprada'];     // Cantidad comprada del producto
    $precioCompraUnidad = $_POST['precioCompraUnidad']; // Precio de compra por unidad
    $proveedorId = $_POST['proveedorId'];               // ID del proveedor asociado

    // Inserta la nueva entrada en la tabla "EntradaProductos"
    $query = "INSERT INTO EntradaProductos (fechaEntrada, productoId, cantidadComprada, precioCompraUnidad, proveedorId) 
              VALUES ('$fechaEntrada', '$productoId', '$cantidadComprada', '$precioCompraUnidad', '$proveedorId')";
    $result = mysqli_query($conn, $query); // Ejecuta la consulta

    // Si ocurre un error en la inserción, se detiene el script y muestra el error
    if (!$result) {
        die("Query Failed: " . mysqli_error($conn));
    }

    // Actualiza la cantidad disponible del producto sumando la cantidad ingresada
    $query_update = "UPDATE Productos SET cantidad = cantidad + $cantidadComprada WHERE idProducto = '$productoId'";
    $result_update = mysqli_query($conn, $query_update); // Ejecuta la actualización

    // Si ocurre un error al actualizar el stock, se detiene el script y muestra el error
    if (!$result_update) {
        die("Query Failed: " . mysqli_error($conn));
    }

    // Define un mensaje de éxito que se mostrará en el dashboard
    $_SESSION['message'] = 'Entrada registrada y cantidad actualizada correctamente';
    $_SESSION['message_type'] = 'success';

    // Redirige al panel principal después de guardar los datos
    header("Location: dashboard.php");
}
?>
