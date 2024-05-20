<?php
include("db.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM Productos WHERE idProducto = $id";
    if ($conn->query($sql) === TRUE) {
        echo "Registro eliminado exitosamente.";
    } else {
        echo "Error eliminando registro: " . $conn->error;
    }
    $conn->close();
    header("Location: dashboard.php");
}
?>