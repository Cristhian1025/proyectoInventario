<?php


if (!isset($_SESSION['nombreUsuario'])) {  // Asegúrate de usar el mismo nombre de sesión
    header("Location: index.php"); // Redirige al inicio de sesión si no está autenticado
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Papelería</title>
    
    <!-- Uso de bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://kit.fontawesome.com/56435b6968.js" crossorigin="anonymous"></script>
</head>
<body>

<header class="navbar navbar-dark bg-dark">
    <div class="container">
        <img src="imagenes/logo1.png" alt="LOGO-DEFECTO" class="rounded" width="60" height="60">
        <a href="dashboard.php" class="navbar-brand"><h1>CONTROL INVENTARIO</h1></a>
        <div class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="imagenes/icon_user.png" alt="Usuario" class="rounded-circle" width="45" height="45">
                <span class="text-white"><?php echo $_SESSION['nombreUsuario']; ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="#" class="dropdown-item">Perfil</a>
                <a href="logout.php" class="dropdown-item">Cerrar sesión</a>
            </div>
        </div>
    </div>
</header>

<nav id="mainNav" class="navbar-expand-md navbar-dark bg-dark">
    <ul class="flex-column mt-3">
        <a href="dashboard.php" class="caja_nav icon-link icon-link-hover">Inicio</a>
        <a href="productos.php" class="caja_nav">Productos</a>
        <a href="proveedores.php" class="caja_nav">proveedores</a>
        <a href="entradaproductos.php" class="caja_nav">Entrada de Productos</a>
        <a href="ventas.php" class="caja_nav">Registrar Venta</a>
    </ul>
</nav>

<script>
    window.addEventListener('scroll', function() {
        const mainNav = document.getElementById('mainNav');
        const fixedNavSpace = document.querySelector('.fixed-nav-space');
        const offset = 60; // Altura de la barra de navegación

        if (window.pageYOffset > offset) {
            mainNav.classList.add('fixed-top');
            if (!fixedNavSpace) {
                const spaceDiv = document.createElement('div');
                spaceDiv.classList.add('fixed-nav-space');
                mainNav.parentNode.insertBefore(spaceDiv, mainNav);
            }
        } else {
            mainNav.classList.remove('fixed-top');
            const fixedNavSpace = document.querySelector('.fixed-nav-space');
            if (fixedNavSpace) {
                fixedNavSpace.parentNode.removeChild(fixedNavSpace);
            }
        }
    });
</script>
