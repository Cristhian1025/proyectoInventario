<?php
/**
 * index.php
 *
 * Página principal de acceso al sistema de Inventario Web.
 * 
 * Este archivo presenta el formulario de inicio de sesión para los usuarios.
 * Verifica si ya existe una sesión activa y, en caso afirmativo, redirige al panel principal.
 * También muestra mensajes de error o confirmación relacionados con el inicio de sesión.
 */

session_start(); // Inicia o reanuda la sesión actual del usuario.

// Verifica si el usuario ya ha iniciado sesión.
// Si existe una sesión activa, se redirige automáticamente al panel principal.
if (isset($_SESSION['nombreUsuario'])) {
    header("Location: dashboard.php");
    exit();
}

// Inicializa variables para mostrar mensajes (por ejemplo, errores o confirmaciones).
$message = null;
$message_type = 'danger'; // Valor por defecto en caso de no especificarse un tipo de mensaje.

// Si existen mensajes almacenados en la sesión, los recupera y luego los limpia.
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    if (isset($_SESSION['message_type'])) {
        $message_type = $_SESSION['message_type']; // Obtiene el tipo de mensaje (éxito, error, advertencia, etc.).
    }

    // Limpia los mensajes de la sesión para evitar repeticiones.
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar - Inventario Web</title>

    <!-- Carga de íconos Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <!-- Enlace a la hoja de estilos principal -->
    <link rel="stylesheet" href="css/style_1.css">
</head>

<body>
    <!-- Contenedor principal con descripción -->
    <div class="screen-1">
        <div class="caja1">
            <div class="name">
                <h1>INVENTARIO EN LA WEB</h1>
            </div>
            <p>Acceda y gestione su inventario <br> de su negocio desde el navegador.</p>
        </div>
    </div>

    <!-- Formulario de inicio de sesión -->
    <div class="screen-1">
        <form class="formulario" action="login.php" method="POST">
            
            <!-- Muestra mensajes de sesión (éxito o error) si existen -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Logo del sistema -->
            <div class="logo">
                <img title="loginIMG" class="logoindex" src="imagenes/inventario.jpg" alt="Logo del Sistema de Inventario" width="300" height="auto">
            </div>

            <!-- Campo de nombre de usuario -->
            <div class="email">
                <label for="usuario">Nombre de Usuario</label>
                <div class="sec-2">
                    <input type="text" id="usuario" name="usuario" placeholder="Username" required/>
                </div>
            </div>

            <!-- Campo de contraseña -->
            <div class="password">
                <label for="contrasenia">Contraseña</label>
                <div class="sec-2">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input class="pas" type="password" id="contrasenia" name="contrasenia" required placeholder="············"/>
                </div>
            </div>

            <!-- Botón de ingreso -->
            <button type="submit" class="login" name="login">Ingresar</button>

            <!-- Pie del formulario (puede usarse para enlaces u otra información) -->
            <div class="footer">
            </div>
        </form>
    </div>
</body>
</html>
