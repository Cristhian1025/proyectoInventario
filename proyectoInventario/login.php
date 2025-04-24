<?php
session_start(); // Inicia o reanuda una sesión existente
include("db.php");

if (isset($_POST["login"])) {
    $nombreUsuario = $_POST["usuario"];
    $contrasenia = $_POST["contrasenia"];

    $sql = "SELECT * FROM Usuario WHERE nombreUsuario = ? AND contrasenia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nombreUsuario, $contrasenia);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) { // Credenciales correctas
        $_SESSION['nombreUsuario'] = $nombreUsuario; // Guarda el nombre de usuario en la sesión
        header("Location: dashboard.php"); // Redirige al dashboard
        exit();
    } else { // Credenciales incorrectas
        $_SESSION['message'] = 'Credenciales incorrectas'; // Almacena el mensaje de error en la sesión
        $_SESSION['message_type'] = 'danger'; // Tipo de mensaje
        header("Location: index.php"); // Redirige al inicio de sesión
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
