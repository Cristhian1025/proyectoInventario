<?php
/**
 * code.php
 *
 * Archivo de ejemplo que realiza una verificación muy básica de credenciales
 * enviadas por un formulario. Añadido encabezado de documentación en español.
 * No cambia la lógica original.
 */
/**
 * code.php
 *
 * Archivo de ejemplo que realiza una verificación muy básica de credenciales
 * enviadas por un formulario. Este archivo está documentado en español.
 *
 * Explicación breve:
 * - Define las credenciales esperadas ($usuarioR, $contraseñaR).
 * - Si la petición es POST y se envían 'email' y 'password', compara los valores
 *   con las credenciales esperadas.
 * - Si las credenciales coinciden, redirige al usuario (ejemplo: Google).
 * - Si no coinciden o faltan datos, muestra mensajes de error.
 *
 * Seguridad: Este código usa credenciales en texto plano y está pensado solo
 * como ejemplo. No usar en producción. En entornos reales:
 * - Almacenar contraseñas con hashing (password_hash / password_verify).
 * - Usar HTTPS y controles de sesión.
 * - Validar y sanitizar todas las entradas del usuario.
 */

// Credenciales esperadas (ejemplo)
$usuarioR = "usuario";
$contraseñaR = "contraseña";

/**
 * Autentica al usuario comparando las credenciales proporcionadas con las esperadas.
 *
 * @param string $email Correo o nombre de usuario enviado por el formulario.
 * @param string $password Contraseña enviada por el formulario.
 * @param string $expectedUser Usuario esperado (valor estático o desde configuración).
 * @param string $expectedPass Contraseña esperada (valor estático o desde configuración).
 * @return bool Devuelve true si las credenciales coinciden exactamente, false en caso contrario.
 *
 * Nota: La comparación es estricta (===). En producción se debe usar un método
 * seguro de verificación (por ejemplo, password_verify para contraseñas hasheadas).
 */
function autenticar_usuario($email, $password, $expectedUser, $expectedPass)
{
    return ($email === $expectedUser && $password === $expectedPass);
}


// Manejo de la petición
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica si se recibieron los campos necesarios desde el formulario
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Obtiene los valores enviados por el formulario
        $usuario = $_POST['email'];
        $contraseña = $_POST['password'];

        // Llama a la función de autenticación (con docstring en español)
        if (autenticar_usuario($usuario, $contraseña, $usuarioR, $contraseñaR)) {
            // Credenciales correctas: redirige al destino (ejemplo)
            header("Location: https://www.google.com");
            exit;
        } else {
            // Credenciales incorrectas: muestra mensaje de error
            echo "Los datos ingresados son incorrectos.";
        }
    } else {
        // Faltan campos en la petición POST
        echo "Ingresar el nombre de usuario y la contraseña.";
    }
} else {
    // No se recibió una petición POST
    echo "Acceso no autorizado.";
}

?>