<?php
include("db.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM Proveedores WHERE idProveedor = $id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = 'Proveedor eliminado correctamente';
        $_SESSION['message_type'] = 'danger';
    } else {
        echo "Error eliminando registro: " . $conn->error;
    }
    $conn->close();
    header("Location: dashboard.php");
}
?>