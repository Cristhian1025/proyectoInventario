<?php
session_start(); // Inicia o reanuda una sesión existente
require_once("db.php"); // Incluye el archivo de conexión a la base de datos

if (isset($_POST["login"])) {
    // Limpiamos los datos de entrada para prevenir inyección SQL
    $nombreUsuario = trim($_POST["usuario"]);
    $contrasenia = trim($_POST["contrasenia"]);

    // Verificamos que los campos no estén vacíos
    if (empty($nombreUsuario) || empty($contrasenia)) {
        $_SESSION['message'] = 'Por favor, completa todos los campos';
        $_SESSION['message_type'] = 'warning';
        header("Location: index.php");
        exit();
    }

    // 1. Consulta para obtener el usuario por nombre de usuario
    $sql = "SELECT * FROM Usuario WHERE nombreUsuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nombreUsuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) { // Usuario encontrado
        $usuario = $result->fetch_assoc(); // Obtiene los datos del usuario
        $contrasena_hash = trim($usuario["contrasenia"]); // Obtiene la contraseña hasheada y elimina espacios

        // 2. Verificar la contraseña hasheada
        if (password_verify($contrasenia, $contrasena_hash)) {
            // Contraseña válida: iniciar sesión
            $_SESSION['id_usuario'] = $usuario['tipoUsuario'];
            $_SESSION['nombreUsuario'] = $nombreUsuario;
            header("Location: dashboard.php"); // Redirige al dashboard
            exit();
        } else {
            // Contraseña inválida: error
            $_SESSION['message'] = 'Credenciales incorrectas';
            $_SESSION['message_type'] = 'danger';
            header("Location: index.php");
            exit();
        }
    } else {
        // Usuario no encontrado: error
        $_SESSION['message'] = 'Credenciales IncorrectasU';
        $_SESSION['message_type'] = 'danger';
        header("Location: index.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>