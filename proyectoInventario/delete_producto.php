<?php
include("db.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM Productos WHERE idProducto = $id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = 'Producto eliminado correctamente';
        $_SESSION['message_type'] = 'danger';
        
    } else {
        echo "Error eliminando registro: " . $conn->error;
    }
    $conn->close();
    header("Location: dashboard.php");
}
?>