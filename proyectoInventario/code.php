<?php
$usuarioR = "usuario";
$contraseñaR = "contraseña";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica si se recibieron datos de usuario y contraseña
    if (isset($_POST['email']) && isset($_POST['password'])) {
        //Obtiene los datos enviados
        $usuario = $_POST['email'];
        $contraseña = $_POST['password'];
        // Verifica si los datos coinciden con los esperados
        if ($usuario === $usuarioR && $contraseña === $contraseñaR) {

            header("Location: https://www.google.com");
            exit;
        } else {
            // Si los datos no coinciden, muestra un mensaje de error
            echo "Los datos ingresados son incorrectos.";
        }
    } else {
        // Si no se recibieron todos los datos necesarios, muestra un mensaje de error
        echo "Ingresar el nombre de usuario y la contraseña.";
    }
} else {
    // Si no se envió ningún dato mediante POST, muestra un mensaje de error
    echo "Acceso no autorizado.";
}
?>