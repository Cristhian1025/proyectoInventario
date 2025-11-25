<?php

$inactivity_timeout = 1800; //Segundos permitidos de inactividad - 30 minutos = 1800
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $inactivity_timeout)) {

    session_unset();     
    //session_destroy();   // Destruir sesión -> pero no imprime mensaje
    // Mensaje a imprimir
    $_SESSION['message'] = 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión de nuevo.';
    $_SESSION['message_type'] = 'Warning';
    session_write_close();
    header("Location: index.php"); // Redirigir a la página de inicio de sesión
    exit();
}

// Actualizar el timestamp de la última actividad a la hora actual
$_SESSION['LAST_ACTIVITY'] = time();


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

<div class="glass-header">
    <!-- Top Bar: Logo & User -->
    <!-- Top Bar: Logo & User -->
    <div class="header-top">
        <div class="container position-relative d-flex justify-content-between align-items-center">
            <!-- Left: Logo -->
            <div class="d-flex align-items-center">
                <img src="imagenes/logo1.png" alt="LOGO" class="rounded shadow-sm" width="50" height="50">
            </div>

            <!-- Center: Title (Absolute) -->
            <div class="position-absolute top-50 start-50 translate-middle text-center">
                <a href="dashboard.php" class="navbar-brand m-0"><h1>CONTROL INVENTARIO</h1></a>
            </div>
            
            <!-- Right: User Dropdown -->
            <div class="dropdown">
                <div class="user-profile d-flex align-items-center dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="imagenes/icon_user.png" alt="Usuario" class="rounded-circle border border-2 border-light" width="40" height="40">
                    <span class="text-white ms-2 fw-bold d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['nombreUsuario']); ?></span>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><a href="#" class="dropdown-item"><i class="fas fa-user me-2"></i> Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a href="logout.php" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <div class="header-nav">
        <div class="container">
            <nav class="d-flex justify-content-center flex-wrap">
                <a href="dashboard.php" class="nav-link-custom"><i class="fas fa-home me-1"></i> Inicio</a>
                <a href="productos.php" class="nav-link-custom"><i class="fas fa-box me-1"></i> Productos</a>
                <a href="proveedores.php" class="nav-link-custom"><i class="fas fa-truck me-1"></i> Proveedores</a>
                <a href="entradaproductos.php" class="nav-link-custom"><i class="fas fa-dolly me-1"></i> Entradas</a>
                <a href="ventas.php" class="nav-link-custom"><i class="fas fa-cash-register me-1"></i> Vender</a>
                <a href="listado_ventas.php" class="nav-link-custom"><i class="fas fa-list me-1"></i> Historial</a>
                <a href="informes.php" class="nav-link-custom"><i class="fas fa-chart-bar me-1"></i> Informes</a>
            </nav>
        </div>
    </div>
</div>
