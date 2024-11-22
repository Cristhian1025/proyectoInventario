<?php    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        echo "Guardando DATOS";
        include("db.php");

        $nombreProducto = $_POST['nombreProducto'];
        $descripcionProducto = $_POST['descripcionProducto'];
        $cantidad = $_POST['cantidad'];
        $precioVenta = $_POST['precioVenta'];
        $precioCompra = $_POST['precioCompra'];
        $proveedorId = $_POST['proveedorId'];
        $CategoriaId = $_POST['CategoriaId'];
    
        $sql = "INSERT INTO Productos (nombreProducto, descripcionProducto, cantidad, precioVenta, precioCompra, proveedorId, CategoriaId)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiddii", $nombreProducto, $descripcionProducto, $cantidad, $precioVenta, $precioCompra, $proveedorId, $CategoriaId);
    
        if ($stmt->execute()) {
            $_SESSION['message'] = 'producto ingresado correctamente';
            $_SESSION['message_type'] = 'success';
            header("location: dashboard.php");

        } else {
            echo "Error:<hr> " . $sql . "<hr>" . $conn->error;
        }
    
        $stmt->close();
        $conn->close();
}
else{
echo "Error, datos no recibidos aaaaaaaaaaaaaa";
}
?>
