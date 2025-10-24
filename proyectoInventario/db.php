<?php
/**
 * db.php
 *
 * Archivo de conexión a la base de datos MySQL.
 * 
 * Este archivo establece la conexión con la base de datos del sistema de inventario.
 * Incluye una verificación de sesión para evitar advertencias si la sesión
 * ya fue iniciada en otro archivo.
 *
 * ⚠️ Recomendación:
 * Mantén las credenciales (usuario, contraseña, host y nombre de base de datos)
 * en un archivo seguro fuera del repositorio público.
 */

// Iniciar la sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Parámetros de conexión
$host = 'localhost';
$user = 'root';
$password = '0000'; // ← tu contraseña
$database = 'inventario';

// Establecer conexión con MySQL
$conn = mysqli_connect($host, $user, $password, $database);

// Verificar conexión
if (!$conn) {
    die("❌ Conexión fallida: " . mysqli_connect_error());
} else {
    // Puedes dejar esta línea solo durante pruebas
    // echo "✅ Conexión exitosa a la base de datos.";
}
?>

