<?php
session_start(); // Inicia o reanuda una sesión existente para poder usar variables de sesión (por ejemplo, guardar datos del usuario logueado)
require_once("db.php"); // Incluye el archivo de conexión a la base de datos (debe crear la variable $conn con la conexión activa)

// Verifica si el formulario de login fue enviado
if (isset($_POST["login"])) {

    // Limpieza de datos para evitar ataques de inyección SQL o espacios innecesarios
    $nombreUsuario = trim($_POST["usuario"]);      // Elimina espacios antes y después del nombre de usuario
    $contrasenia = trim($_POST["contrasenia"]);    // Elimina espacios antes y después de la contraseña

    // Validación básica: los campos no deben estar vacíos
    if (empty($nombreUsuario) || empty($contrasenia)) {
        $_SESSION['message'] = 'Por favor, completa todos los campos'; // Mensaje de error para mostrar en la página principal
        $_SESSION['message_type'] = 'warning'; // Tipo de alerta (Bootstrap)
        header("Location: index.php"); // Redirige al login
        exit(); // Detiene la ejecución del script
    }

    // 1️⃣ Consulta SQL para buscar un usuario con el nombre ingresado
    $sql = "SELECT * FROM Usuario WHERE nombreUsuario = ?";
    $stmt = $conn->prepare($sql); // Prepara la consulta para evitar inyecciones SQL
    $stmt->bind_param("s", $nombreUsuario); // Asigna el parámetro (s = string)
    $stmt->execute(); // Ejecuta la consulta
    $result = $stmt->get_result(); // Obtiene el resultado de la consulta

    // Si se encuentra exactamente un usuario con ese nombre
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc(); // Obtiene la fila de datos como arreglo asociativo
        $contrasena_hash = trim($usuario["contrasenia"]); // Obtiene la contraseña en la base de datos (ya hasheada) y elimina espacios

        // 2️⃣ Verifica la contraseña ingresada comparándola con el hash almacenado
        if (password_verify($contrasenia, $contrasena_hash)) {
            // Si la contraseña es válida, se inician variables de sesión
            //
            $_SESSION['id_usuario'] = $usuario['tipoUsuario']; // Guarda el tipo de usuario (probablemente Admin, Vendedor, etc.)
            $_SESSION['nombreUsuario'] = $nombreUsuario; // Guarda el nombre del usuario logueado

            // Redirige al panel principal después de un login exitoso
            header("Location: dashboard.php");
            exit();
        } else {
            // Si la contraseña no coincide
            $_SESSION['message'] = 'Credenciales incorrectas';
            $_SESSION['message_type'] = 'danger';
            header("Location: index.php"); // Redirige al login
            exit();
        }
    } else {
        // Si no se encuentra el usuario
        $_SESSION['message'] = 'Credenciales IncorrectasU'; // Mensaje de error (la "U" parece un error de tipeo)
        $_SESSION['message_type'] = 'danger';
        header("Location: index.php");
        exit();
    }

    // Cierra los recursos usados
    $stmt->close();
    $conn->close();
}
?>
