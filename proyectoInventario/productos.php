<?php 
    include("db.php");
  include("includes/header.php"); 

  // Obtenemos y guardamos los proveedores
$query_proveedores = "SELECT idProveedor, nombreProveedor FROM Proveedores";
$result_proveedores = mysqli_query($conn, $query_proveedores);
$proveedores = mysqli_fetch_all($result_proveedores, MYSQLI_ASSOC);

// Obtenemo y guardamos las categorías
$query_categorias = "SELECT idCategoria, nombreCategoria FROM Categorias";
$result_categorias = mysqli_query($conn, $query_categorias);
$categorias = mysqli_fetch_all($result_categorias, MYSQLI_ASSOC);
?>
    

    <div class="container mt-5">
    <h2 class="mb-4">Ingreso de Nuevos Productos</h2>
    <form action="save_producto.php" method="POST">
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
            <label for="proveedorId">Proveedor</label>
            <select class="form-control" id="proveedorId" name="proveedorId" required>
                <option value="">Selecciona un proveedor</option>
                <?php foreach ($proveedores as $proveedor): ?>  //Por cada valor, añadimos una opción
                    <option value="<?php echo $proveedor['idProveedor']; ?>">
                        <?php echo $proveedor['nombreProveedor']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="CategoriaId">Categoría</label>
            <select class="form-control" id="CategoriaId" name="CategoriaId" required>
                <option value="">Selecciona una categoría</option>
                <?php foreach ($categorias as $categoria): ?> //igual, Por cada valor, añadimos una opción
                    <option value="<?php echo $categoria['idCategoria']; ?>">
                        <?php echo $categoria['nombreCategoria']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>
<?php  include("includes/footer.php") ?>