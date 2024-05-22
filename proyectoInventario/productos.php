<?php 
    include("db.php");
  include("includes/header.php") ?>
    

    <div class="container mt-5">
    <h2 class="mb-4">Ingreso de Nuevos Productos</h2>
    <form action="save_producto.php" method="POST">
        <div class="form-group">
            <label for="idProducto">ID Producto</label>
            <input type="number" class="form-control" id="idProducto" name="idProducto" required>
        </div>
        <div class="form-group">
            <label for="nombreProducto">Nombre Producto</label>
            <input type="text" class="form-control" id="nombreProducto" name="nombreProducto" maxlength="45" required>
        </div>
        <div class="form-group">
            <label for="descripcionProducto">Descripción Producto</label>
            <textarea class="form-control" id="descripcionProducto" name="descripcionProducto" maxlength="120" required></textarea>
        </div>
        <div class="form-group">
            <label for="cantidad">Cantidad</label>
            <input type="number" class="form-control" id="cantidad" name="cantidad" required>
        </div>
        <div class="form-group">
            <label for="precioVenta">Precio de Venta</label>
            <input type="number" step="0.01" class="form-control" id="precioVenta" name="precioVenta" required>
        </div>
        <div class="form-group">
            <label for="precioCompra">Precio de Compra</label>
            <input type="number" step="0.01" class="form-control" id="precioCompra" name="precioCompra" required>
        </div>
        <div class="form-group">
            <label for="proveedorId">ID Proveedor</label>
            <input type="number" class="form-control" id="proveedorId" name="proveedorId" required>
        </div>
        <div class="form-group">
            <label for="CategoriaId">ID Categoría</label>
            <input type="number" class="form-control" id="CategoriaId" name="CategoriaId" required>
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>
<?php  include("includes/footer.php") ?>