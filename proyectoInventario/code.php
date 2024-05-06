<?php

$usuarioR = "usuario";
$contraseñaR = "contraseña";

// Verifica si se enviaron datos mediante el método POST

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica si se recibieron datos de usuario y contraseña
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Obtiene los datos enviados
        $usuario = $_POST['email'];
        $contraseña = $_POST['password'];
        // Verifica si los datos coinciden con los esperados
        if ($usuario === $usuarioR && $contraseña === $contraseñaR) {
            // Redirecciona a google.com
            header("Location: https://www.google.com");
            exit;
        } else {
            // Si los datos no coinciden, muestra un mensaje de error
            echo "Los datos ingresados son incorrectos.";
        }
    } else {
        // Si no se recibieron todos los datos necesarios, muestra un mensaje de error
        echo "Por favor, ingresa tanto el nombre de usuario como la contraseña.";
    }
} else {
    // Si no se envió ningún dato mediante POST, muestra un mensaje de error
    echo "Acceso no autorizado.";
}
?>