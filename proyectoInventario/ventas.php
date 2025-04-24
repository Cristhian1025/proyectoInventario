<?php 
include("db.php");

// Obtener los productos
$query_productos = "SELECT idProducto, nombreProducto FROM Productos";
$result_productos = mysqli_query($conn, $query_productos);
$productos = mysqli_fetch_all($result_productos, MYSQLI_ASSOC);

// Obtener los usuarios
$query_usuario = "SELECT idUsuario, nombreCompleto FROM usuario";
$result_usuario = mysqli_query($conn, $query_usuario);
$usuarios = mysqli_fetch_all($result_usuario, MYSQLI_ASSOC);
?>

<?php include("includes/header.php") ?>

<div class="container mt-5">
    <h2 class="mb-4">Registrar Ventas</h2>
    <form action="save_venta.php" method="POST">
        <div class="form-group">
            <label for="fechaVenta">Fecha de Venta</label>
            <input type="date" class="form-control" id="fechaVenta" name="fechaVenta" required>
        </div>

        <div id="productos-container">
            <div class="producto-item">
                <div class="form-group">
                    <label for="productoId">Producto</label>
                    <select class="form-control" name="productoId[]" required>
                        <option value="">Selecciona un producto</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['idProducto']; ?>">
                                <?php echo $producto['nombreProducto']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cantidadVenta">Cantidad Vendida</label>
                    <input type="number" class="form-control" name="cantidadVenta[]" required>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-primary mb-3" id="add-product">Agregar Producto</button>

        <div class="form-group">
            <label for="usuarioId">Vendedor</label>
            <select class="form-control" id="vendedorId" name="vendedorId" required>
                <option value="">Selecciona un vendedor</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario['idUsuario']; ?>">
                        <?php echo $usuario['nombreCompleto']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <input type="submit" class="btn btn-success btn-block" name="save_venta" value="Registrar Ventas">
    </form>
</div>

<script>
    document.getElementById('add-product').addEventListener('click', () => {
        const container = document.getElementById('productos-container');
        const newProduct = document.querySelector('.producto-item').cloneNode(true);
        newProduct.querySelector('select').value = '';
        newProduct.querySelector('input').value = '';
        container.appendChild(newProduct);
    });
</script>

<?php include("includes/footer.php") ?>