<?php
/**
 * save_producto.php
 *
 * Descripción:
 * Este script procesa el formulario de registro de nuevos productos y los guarda en la base de datos.
 * Utiliza consultas preparadas para evitar inyecciones SQL.
 */

// Verifica si la solicitud fue enviada mediante el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    echo "Guardando DATOS"; // Mensaje de depuración para confirmar recepción de datos

    // Incluye el archivo de conexión a la base de datos
    include("db.php");

    // Captura los datos enviados desde el formulario
    $nombreProducto = $_POST['nombreProducto'];           // Nombre del producto
    $descripcionProducto = $_POST['descripcionProducto']; // Descripción del producto
    $cantidad = $_POST['cantidad'];                       // Cantidad en inventario
    $precioVenta = $_POST['precioVenta'];                 // Precio de venta al público
    $precioCompra = $_POST['precioCompra'];               // Precio de compra al proveedor
    $proveedorId = $_POST['proveedorId'];                 // ID del proveedor asociado
    $CategoriaId = $_POST['CategoriaId'];                 // ID de la categoría del producto

    // Prepara la sentencia SQL para insertar los datos en la tabla "Productos"
    $sql = "INSERT INTO Productos (nombreProducto, descripcionProducto, cantidad, precioVenta, precioCompra, proveedorId, CategoriaId)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Prepara la consulta para evitar inyecciones SQL
    $stmt = $conn->prepare($sql);

    // Asocia los valores a los parámetros de la consulta
    // s = string, i = integer, d = double
    $stmt->bind_param("ssiddii", $nombreProducto, $descripcionProducto, $cantidad, $precioVenta, $precioCompra, $proveedorId, $CategoriaId);

    // Ejecuta la consulta y verifica si fue exitosa
    if ($stmt->execute()) {
        // Si se guarda correctamente, define un mensaje de éxito y redirige al dashboard
        $_SESSION['message'] = 'producto ingresado correctamente';
        $_SESSION['message_type'] = 'success';
        header("location: dashboard.php");
    } else {
        // Si ocurre un error, muestra información de depuración
        echo "Error:<hr> " . $sql . "<hr>" . $conn->error;
    }

    // Cierra la consulta y la conexión
    $stmt->close();
    $conn->close();

} else {
    // Si el script se ejecuta sin recibir datos POST, muestra mensaje de error
    echo "Error, datos no recibidos aaaaaaaaaaaaaa";
}
?>
