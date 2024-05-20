<?php 
    include("db.php");
?>

<?php  include("includes/header.php") ?>
    <header>
        <h1>Inventario de Papelería</h1>
    </header>
    <nav> 
        <a href="dashboard.html" class="caja_nav">Inicio</a>
        <a href="#" class="caja_nav">Empleados</a>
        <a href="productos.html" class="caja_nav" style="background-color: blue; color: aliceblue;">Productos</a>
        <a href="proveedores.html" class="caja_nav">proveedores</a>
    </nav>

    <h2>PRODUCTOS</h2>
    
    <div class="container mt-5">
    <h2 class="mb-4">Ingreso de productos</h2>
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
    
    <footer>
        <p>&copy; 2024 "InventarioInc". Todos los derechos reservados.</p>
    </footer>

</body>
</html>