<?php

$conn = mysqli_connect(
    'localhost',
    'root',
    '1025',
    'inventario'
);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

?>
