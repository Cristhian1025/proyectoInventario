<?php 
    include("db.php");
?>

<!--
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Papelería</title>
    <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap'>
    <link rel="stylesheet" href="./css/style_1.css">

</head>
<body>
-->

<?php  include("includes/header.php") ?>

    <header>
        <h1>Inventario de Papelería</h1>
    </header>
    <div class="container mt-5">
    <h2 class="mb-4">Crear Nuevo Proveedor</h2>

    <form action="save_proveedor.php" method="POST">

        <div class="form-group">
            <label for="idProveedor">Id del Proveedor</label>
            <input type="number" class="form-control" id="idProveedor" name="idProveedor" required>
        </div>
        <div class="form-group">
            <label for="nombreProveedor">Nombre Proveedor</label>
            <input type="text" class="form-control" id="nombreProveedor" name="nombreProveedor" required>
        </div>
        <div class="form-group">
            <label for="descripcionProveedor">Descripción Proveedor</label>
            <textarea class="form-control" id="descripcionProveedor" name="descripcionProveedor" required></textarea>
        </div>
        <div class="form-group">
            <label for="direccionProveedor">Dirección Proveedor</label>
            <input type="text" class="form-control" id="direccionProveedor" name="direccionProveedor" required>
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" required>
        </div>
        <div class="form-group">
            <label for="Correo">Correo</label>
            <input type="email" class="form-control" id="Correo" name="Correo" required>
        </div>
        <div class="form-group">
            <label for="infoAdicional">Información Adicional</label>
            <textarea class="form-control" id="infoAdicional" name="infoAdicional"></textarea>
        </div>
        <input type="submit" class="btn btn-success btn-block"
        name="save_proveedor" value="Enviar">
    </form>
</div>
    
    <footer>
        <p>&copy; 2024 "InventarioInc". Todos los derechos reservados.</p>
    </footer>

</body>
</html>