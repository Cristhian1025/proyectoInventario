<?php
include("db.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM Productos WHERE idProducto = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreProducto = $_POST['nombreProducto'];
    $descripcionProducto = $_POST['descripcionProducto'];
    $cantidad = $_POST['cantidad'];
    $precioVenta = $_POST['precioVenta'];
    $precioCompra = $_POST['precioCompra'];
    $proveedorId = $_POST['proveedorId'];
    $CategoriaId = $_POST['CategoriaId'];

    $sql = "UPDATE Productos SET nombreProducto='$nombreProducto', descripcionProducto='$descripcionProducto', cantidad='$cantidad', precioVenta='$precioVenta', precioCompra='$precioCompra', proveedorId='$proveedorId', CategoriaId='$CategoriaId' WHERE idProducto = $id";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = 'Producto actualizado correctamente';
        $_SESSION['message_type'] = 'success';
    } else {
        echo "Error actualizando registro: " . $conn->error;
    }
    $conn->close();
    header("Location: dashboard.php");
}

include("includes/header.php") ?>





<div class="container mt-4">

    <h2>Editar Producto</h2>
    <form action="edit_producto.php?id=<?php echo $id; ?>" method="POST">

        <div class="form-group">
            <label for="nombreProducto">Nombre</label>
            <input type="text" class="form-control" id="nombreProducto" name="nombreProducto" value="<?php echo $row['nombreProducto']; ?>" required>
        </div>
        <div class="form-group">
            <label for="descripcionProducto">Descripción</label>
            <input type="text" class="form-control" id="descripcionProducto" name="descripcionProducto" value="<?php echo $row['descripcionProducto']; ?>" required>
        </div>
        <div class="form-group">
            <label for="cantidad">Cantidad</label>
            <input type="number" class="form-control" id="cantidad" name="cantidad" value="<?php echo $row['cantidad']; ?>" required>
        </div>
        <div class="form-group">
            <label for="precioVenta">Precio Venta</label>
            <input type="text" class="form-control" id="precioVenta" name="precioVenta" value="<?php echo $row['precioVenta']; ?>" required>
        </div>
        <div class="form-group">
            <label for="precioCompra">Precio Compra</label>
            <input type="text" class="form-control" id="precioCompra" name="precioCompra" value="<?php echo $row['precioCompra']; ?>" required>
        </div>
        <div class="form-group">
            <label for="proveedorId">Proveedor ID</label>
            <input type="number" class="form-control" id="proveedorId" name="proveedorId" value="<?php echo $row['proveedorId']; ?>" required>
        </div>
        <div class="form-group">
            <label for="CategoriaId">Categoría ID</label>
            <input type="number" class="form-control" id="CategoriaId" name="CategoriaId" value="<?php echo $row['CategoriaId']; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary mx-4 my-4">Actualizar</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>