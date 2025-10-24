<?php
/**
 * DB/db.php
 *
 * Archivo de conexión a la base de datos (copia/alternativa). Encabezado en español añadido.
 */

session_start();

$conn = mysqli_connect(
    'localhost',
    'root',
    '0000',
    'inventario'
);

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}
?>
