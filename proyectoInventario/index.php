<?php
session_start(); // Inicia la sesión

// Redirigir si ya está logueado
if (isset($_SESSION['nombreUsuario'])) {
    header("Location: dashboard.php");
    exit();
}

// Preparar variables para el mensaje (si existe)
$message = null;
$message_type = 'danger'; //Por defecto si no se especifica
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    if (isset($_SESSION['message_type'])) {
        $message_type = $_SESSION['message_type']; // Obtener el tipo real
    }

    unset($_SESSION['message']);
    unset($_SESSION['message_type']); // Limpiar ambos
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar - Inventario Web</title> <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="css/style_1.css">
    
</head>

<body>
<div class="screen-1">
    <div class="caja1">
        <div class="name">
            <h1>INVENTARIO EN LA WEB</h1>
        </div>
        <p>Acceda y gestione su inventario <br> de su negocio desde el navegador.</p>
    </div>
</div>

<div class="screen-1">
    <form class="formulario" action="login.php" method="POST">
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
                 </div>
        <?php endif; ?>

        <div class="logo">
            <img title="loginIMG" class="logoindex" src="imagenes/inventario.jpg" alt="Logo del Sistema de Inventario" width="300" height="auto">
        </div>

        <div class="email">
            <label for="usuario">Nombre de Usuario</label>
            <div class="sec-2">
                <input type="text" id="usuario" name="usuario" placeholder="Username" required/>
            </div>
        </div>

        <div class="password">
            <label for="contrasenia">Contraseña</label>
            <div class="sec-2">
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input class="pas" type="password" id="contrasenia" name="contrasenia" required placeholder="············"/>
            </div>
        </div>

        <button type="submit" class="login" name="login">Ingresar</button>

        <div class="footer">
            </div>
    </form>
</div>

</body>
</html>