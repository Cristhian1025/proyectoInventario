<?php 
    include("db.php");
?>
<?php  include("includes/header.php") ?>

    <div class="container mt-5">
    <h2 class="mb-4">Crear Nuevo Proveedor</h2>
    <form action="save_proveedor.php" method="POST">

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
    
<?php  include("includes/footer.php") ?>