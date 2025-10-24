<?php
/**
 * edit_producto.php
 *
 * Página para editar un producto existente en la base de datos.
 * 
 * Flujo:
 * 1. Si se recibe un ID por método GET, se obtienen los datos del producto desde la base de datos.
 * 2. Si el formulario se envía (POST), se actualizan los campos del producto.
 * 3. Finalmente, redirige al dashboard con un mensaje de confirmación.
 *
 * Requiere:
 * - db.php: para la conexión con la base de datos.
 * - includes/header.php: para la cabecera de la página.
 */

// ─────────────────────────────────────────────
// INCLUSIÓN DE DEPENDENCIAS
// ─────────────────────────────────────────────
include("db.php"); // Conexión a la base de datos

// ─────────────────────────────────────────────
// OBTENER DATOS DEL PRODUCTO (MÉTODO GET)
// ─────────────────────────────────────────────
if (isset($_GET['id'])) {
    $id = $_GET['id']; // Se obtiene el ID del producto desde la URL

    // Consulta SQL para obtener los datos del producto
    $sql = "SELECT * FROM Productos WHERE idProducto = $id";

    // Ejecutar la consulta
    $result = $conn->query($sql);

    // Almacenar los resultados en un arreglo asociativo
    $row = $result->fetch_assoc();
}

// ─────────────────────────────────────────────
// ACTUALIZAR DATOS DEL PRODUCTO (MÉTODO POST)
// ─────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar los valores enviados desde el formulario
    $nombreProducto      = $_POST['nombreProducto'];
    $descripcionProducto = $_POST['descripcionProducto'];
    $cantidad            = $_POST['cantidad'];
    $precioVenta         = $_POST['precioVenta'];
    $precioCompra        = $_POST['precioCompra'];
    $proveedorId         = $_POST['proveedorId'];
    $CategoriaId         = $_POST['CategoriaId'];

    // Consulta SQL para actualizar los datos del producto
    $sql = "UPDATE Productos 
            SET nombreProducto='$nombreProducto', 
                descripcionProducto='$descripcionProducto', 
                cantidad='$cantidad', 
                precioVenta='$precioVenta', 
                precioCompra='$precioCompra', 
                proveedorId='$proveedorId', 
                CategoriaId='$CategoriaId' 
            WHERE idProducto = $id";

    // Ejecutar la actualización
    if ($conn->query($sql) === TRUE) {
        // Crear mensaje de éxito para mostrar en el dashboard
        $_SESSION['message'] = 'Producto actualizado correctamente';
        $_SESSION['message_type'] = 'success';
    } else {
        // Mostrar mensaje de error si ocurre un problema
        echo "Error actualizando registro: " . $conn->error;
    }

    // Cerrar conexión y redirigir al dashboard
    $conn->close();
    header("Location: dashboard.php");
    exit;
}

// ─────────────────────────────────────────────
// INCLUIR ENCABEZADO Y FORMULARIO DE EDICIÓN
// ─────────────────────────────────────────────
include("includes/header.php");
?>

<div class="container mt-4">
    <h2>Editar Producto</h2>

    <!-- Formulario de edición del producto -->
    <form action="edit_producto.php?id=<?php echo $id; ?>" method="POST">
        <div class="form-group">
            <label for="nombreProducto">Nombre</label>
            <input 
                type="text" class="form-control" id="nombreProducto" name="nombreProducto" 
                value="<?php echo $row['nombreProducto']; ?>" required>
        </div>

        <div class="form-group">
            <label for="descripcionProducto">Descripción</label>
            <input 
                type="text" class="form-control" id="descripcionProducto" name="descripcionProducto" 
                value="<?php echo $row['descripcionProducto']; ?>" required>
        </div>

        <div class="form-group">
            <label for="cantidad">Cantidad</label>
            <input 
                type="number" class="form-control" id="cantidad" name="cantidad" 
                value="<?php echo $row['cantidad']; ?>" required>
        </div>

        <div class="form-group">
            <label for="precioVenta">Precio Venta</label>
            <input 
                type="text" class="form-control" id="precioVenta" name="precioVenta" 
                value="<?php echo $row['precioVenta']; ?>" required>
        </div>

        <div class="form-group">
            <label for="precioCompra">Precio Compra</label>
            <input 
                type="text" class="form-control" id="precioCompra" name="precioCompra" 
                value="<?php echo $row['precioCompra']; ?>" required>
        </div>

        <div class="form-group">
            <label for="proveedorId">Proveedor ID</label>
            <input 
                type="number" class="form-control" id="proveedorId" name="proveedorId" 
                value="<?php echo $row['proveedorId']; ?>" required>
        </div>

        <div class="form-group">
            <label for="CategoriaId">Categoría ID</label>
            <input 
                type="number" class="form-control" id="CategoriaId" name="CategoriaId" 
                value="<?php echo $row['CategoriaId']; ?>" required>
        </div>

        <!-- Botón de envío -->
        <button type="submit" class="btn btn-primary mx-4 my-4">Actualizar</button>
    </form>
</div>

