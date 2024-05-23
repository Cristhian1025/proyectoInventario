<?php
$usuario = isset($_GET['usuario']) ? $_GET['usuario'] : "";

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
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand">CONTROL INVENTARIO</a>
            <div class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <img src="imagenes/icon_user.png" alt="Usuario" class="rounded-circle" width="30" height="30">
                    <span class="text-white"><?php echo $usuario; ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="#" class="dropdown-item">Perfil</a>
                    <a href="index.php" class="dropdown-item">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </nav>

<nav class="navbar-expand-md navbar-dark bg-dark">
  <ul class="flex-column mt-5">
    <a href="dashboard.php" class="caja_nav">Inicio</a>
    <a href="productos.php" class="caja_nav">Productos</a>
    <a href="proveedores.php" class="caja_nav">proveedores</a>
    <a href="entradaproductos.php" class="caja_nav">Entrada de Productos</a>
    <a href="ventas.php" class="caja_nav">Registrar Venta</a>
  </ul>
</nav>