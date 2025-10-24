<?php
/**
 * delete_producto.php
 *
 * Script para eliminar un producto de la base de datos.
 * Utiliza consultas preparadas para evitar inyecciones SQL.
 * Guarda mensajes en sesión y redirige al panel principal.
 * 
 * @package Inventario
 * @version 1.0
 */

session_start();
include("db.php");

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Consulta segura usando prepared statement
    $stmt = $conn->prepare("DELETE FROM productos WHERE idProducto = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = '✅ Producto eliminado correctamente';
            $_SESSION['message_type'] = 'danger';
        } else {
            $_SESSION['message'] = '⚠️ Error al eliminar el producto.';
            $_SESSION['message_type'] = 'warning';
            error_log("Error al eliminar producto (ID $id): " . $stmt->error);
        }

        $stmt->close();
    } else {
        error_log("Error preparando consulta de eliminación: " . $conn->error);
    }

    $conn->close();
    header("Location: dashboard.php");
    exit;
} else {
    $_SESSION['message'] = '❌ ID de producto no válido.';
    $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
    exit;
}
?>
