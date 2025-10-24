<?php
/**
 * delete_proveedor.php
 *
 * Script para eliminar un proveedor de la base de datos.
 * Usa consultas preparadas para evitar inyecciones SQL.
 * Muestra mensajes de éxito o error mediante variables de sesión.
 * 
 * @package Inventario
 * @version 1.0
 */

session_start(); // Inicia la sesión para manejar mensajes
include("db.php"); // Conexión a la base de datos

// Verifica si se recibió el parámetro 'id' por la URL y que sea numérico
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id']; // Convierte el ID a número entero

    // Prepara una consulta SQL segura para eliminar el proveedor
    $stmt = $conn->prepare("DELETE FROM proveedores WHERE idProveedor = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id); // Asocia el valor del parámetro
        if ($stmt->execute()) {
            // Si la eliminación fue exitosa
            $_SESSION['message'] = '✅ Proveedor eliminado correctamente';
            $_SESSION['message_type'] = 'danger';
        } else {
            // Si hubo un error al ejecutar la consulta
            $_SESSION['message'] = '⚠️ Error al eliminar el proveedor.';
            $_SESSION['message_type'] = 'warning';
            error_log("Error al eliminar proveedor (ID $id): " . $stmt->error);
        }
        $stmt->close();
    } else {
        // Si hubo un error al preparar la consulta
        error_log("Error preparando eliminación de proveedor: " . $conn->error);
    }

    $conn->close();
    header("Location: dashboard.php");
    exit; // Detiene la ejecución tras redirigir

} else {
    // Si el ID no es válido o no se recibió
    $_SESSION['message'] = '❌ ID de proveedor no válido.';
    $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
    exit;
}
?>
