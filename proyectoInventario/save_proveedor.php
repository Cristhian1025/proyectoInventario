<?php
/**
 * save_proveedor.php
 *
 * Descripción:
 * Este script recibe los datos del formulario de creación de un nuevo proveedor,
 * los valida y los inserta en la base de datos mediante una consulta preparada.
 * También redirige al panel principal si la operación fue exitosa.
 */

// Verifica si el formulario fue enviado correctamente
if (isset($_POST['save_proveedor'])) {
    
    echo "Guardando DATOS"; // Mensaje de depuración para confirmar que los datos llegaron

    // Incluye la conexión a la base de datos
    include("db.php");

    // Captura los valores enviados desde el formulario
    $nombreProveedor = $_POST["nombreProveedor"];           // Nombre del proveedor
    $descripcionProveedor = $_POST["descripcionProveedor"]; // Descripción del proveedor
    $direccionProveedor = $_POST["direccionProveedor"];     // Dirección física del proveedor
    $telefono = $_POST["telefono"];                         // Teléfono de contacto
    $Correo = $_POST["Correo"];                             // Correo electrónico
    $infoAdicional = $_POST["infoAdicional"];               // Información adicional (opcional)

    // Sentencia SQL para insertar los datos en la tabla "Proveedores"
    $sql = "INSERT INTO Proveedores (nombreProveedor, descripcionProveedor, direccionProveedor, telefono, Correo, infoAdicional)
            VALUES (?,?,?,?,?,?)";

    // Prepara la consulta para evitar inyecciones SQL
    $stmt = $conn->prepare($sql);

    // Asocia los valores a los parámetros de la consulta (todos tipo string)
    $stmt->bind_param("ssssss", $nombreProveedor, $descripcionProveedor, $direccionProveedor, $telefono, $Correo, $infoAdicional);

    // Ejecuta la consulta
    if ($stmt->execute()) {
        // Si se guarda correctamente, redirige al dashboard
        header("location: dashboard.php");
    } else {
        // Si ocurre un error, muestra información detallada
        echo "Error:<hr> " . $sql . "<hr>" . $conn->error;
    }

    // Cierra la consulta y la conexión
    $stmt->close();
    $conn->close();

} else {
    // Si no se recibieron datos, muestra un mensaje de error
    echo "Error, datos no recibidos aaaaaaaaaaaaaa";
}
?>
