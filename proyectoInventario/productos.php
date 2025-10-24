<?php
/**
 * productos.php
 *
 * Muestra y gestiona el formulario para el ingreso de nuevos productos.
 * Además, obtiene las listas de proveedores y categorías desde la base de datos
 * para mostrarlas como opciones en los menús desplegables.
 */

include("db.php");              // Conexión a la base de datos
include("includes/header.php"); // Encabezado del sitio (HTML, menú, estilos, etc.)

// 📦 Consulta para obtener todos los proveedores registrados
$query_proveedores = "SELECT idProveedor, nombreProveedor FROM Proveedores";
$result_proveedores = mysqli_query($conn, $query_proveedores);
$proveedores = mysqli_fetch_all($result_proveedores, MYSQLI_ASSOC); // Convierte los resultados en un arreglo asociativo

// 🏷️ Consulta para obtener todas las categorías registradas
$query_categorias = "SELECT idCategoria, nombreCategoria FROM Categorias";
$result_categorias = mysqli_query($conn, $query_categorias);
$categorias = mysqli_fetch_all($result_categorias, MYSQLI_ASSOC); // Convierte los resultados en un arreglo asociativo
?>
    
<!-- 🧾 Contenedor principal del formulario -->
<div class="container mt-5">
    <h2 class="mb-4">Ingreso de Nuevos Productos</h2>

    <!-- Formulario para registrar un nuevo producto -->
    <form action="save_producto.php" method="POST">
        <!-- Campo: Nombre del producto -->
        <div class="form-group">
            <label for="nombreProducto">Nombre Producto</label>
            <input type="text" class="form-control" id="nombreProducto" name="nombreProducto" maxlength="45" required>
        </div>

        <!-- Campo: Descripción del producto -->
        <div class="form-group">
            <label for="descripcionProducto">Descripción Producto</label>
            <textarea class="form-control" id="descripcionProducto" name="descripcionProducto" maxlength="120" required></textarea>
        </div>

        <!-- Campo: Cantidad en inventario -->
        <div class="form-group">
            <label for="cantidad">Cantidad</label>
            <input type="number" class="form-control" id="cantidad" name="cantidad" required>
        </div>

        <!-- Campo: Precio de venta -->
        <div class="form-group">
            <label for="precioVenta">Precio de Venta</label>
            <input type="number" step="0.01" class="form-control" id="precioVenta" name="precioVenta" required>
        </div>

        <!-- Campo: Precio de compra -->
        <div class="form-group">
            <label for="precioCompra">Precio de Compra</label>
            <input type="number" step="0.01" class="form-control" id="precioCompra" name="precioCompra" required>
        </div>

        <!-- Menú desplegable: Proveedor -->
        <div class="form-group">
            <label for="proveedorId">Proveedor</label>
            <select class="form-control" id="proveedorId" name="proveedorId" required>
                <option value="">Selecciona un proveedor</option>
                <?php foreach ($proveedores as $proveedor): ?>  
                    <!-- Por cada proveedor, se genera una opción con su ID y nombre -->
                    <option value="<?php echo $proveedor['idProveedor']; ?>">
                        <?php echo $proveedor['nombreProveedor']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Menú desplegable: Categoría -->
        <div class="form-group">
            <label for="CategoriaId">Categoría</label>
            <select class="form-control" id="CategoriaId" name="CategoriaId" required>
                <option value="">Selecciona una categoría</option>
                <?php foreach ($categorias as $categoria): ?> 
                    <!-- Por cada categoría, se genera una opción con su ID y nombre -->
                    <option value="<?php echo $categoria['idCategoria']; ?>">
                        <?php echo $categoria['nombreCategoria']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Botón para enviar el formulario -->
        <button type="submit" class="btn btn-primary mx-4 my-4">Enviar</button>
    </form>
</div>

<?php include("includes/footer.php"); // Pie de página del sitio ?>
