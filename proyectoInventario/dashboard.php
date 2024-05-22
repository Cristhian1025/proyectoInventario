<?php 
    include("db.php");
  
include("includes/header.php") ?>
  <div class="absolute inset-0 -z-10 h-full w-full bg-white bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:6rem_4rem]"><div class="absolute bottom-0 left-0 right-0 top-0 bg-[radial-gradient(circle_500px_at_50%_200px,#C9EBFF,transparent)]"></div></div>
  <div class="absolute inset-0 -z-10 h-full w-full bg-white bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px]"></div>
    <h1>    PANEL DE CONTROL</h1>
    <div class="container mt-4">
    <form action="dashboard.php" method="POST">
        <div class="form-group">
            <label for="opcion">Selecciona una opción:</label>
            <select class="form-control" id="opcion" name="opcion">
                <option value="productos">Productos</option>
                <option value="proveedores">Proveedores</option>
                <option value="ambos">Productos y Proveedores</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Mostrar</button>
    </form>
</div>

<div class="container mt-4">

<!-- CODIGO INCRUSTADO DE PHP -->

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $opcion = $_POST['opcion'];

        if ($opcion == 'productos') {
            $sql = "SELECT * FROM Productos";  //consulta SQL para Prouctos
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<h2>Productos</h2>";
                echo "<table class='table table-bordered'>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Cantidad</th><th>Precio Venta</th><th>Precio Compra</th><th>Proveedor ID</th><th>Categoría ID</th><th>Acciones</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['idProducto']}</td>
                        <td>{$row['nombreProducto']}</td>
                        <td>{$row['descripcionProducto']}</td>
                        <td>{$row['cantidad']}</td>
                        <td>{$row['precioVenta']}</td>
                        <td>{$row['precioCompra']}</td>
                        <td>{$row['proveedorId']}</td>
                        <td>{$row['CategoriaId']}</td>
                        <td>
                            <a href='edit_producto.php?id={$row['idProducto']}'>Editar</a> |
                            <a href='delete_producto.php?id={$row['idProducto']}'>Eliminar</a>
                        </td>
                    </tr>";
                }
                echo "</table>";
            } else {
                echo "No hay productos.";
            }
        } elseif ($opcion == 'proveedores') {
            $sql = "SELECT * FROM Proveedores";  //consulta SQL para Prouctos
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<h2>Proveedores</h2>";
                echo "<table class='table table-bordered'>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Dirección</th><th>Teléfono</th><th>Correo</th><th>Información Adicional</th><th>Acciones</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['idProveedor']}</td>
                        <td>{$row['nombreProveedor']}</td>
                        <td>{$row['descripcionProveedor']}</td>
                        <td>{$row['direccionProveedor']}</td>
                        <td>{$row['telefono']}</td>
                        <td>{$row['Correo']}</td>
                        <td>{$row['infoAdicional']}</td>
                        <td>
                            <a href='edit_proveedor.php?id={$row['idProveedor']}'>Editar</a> |
                            <a href='delete_proveedor.php?id={$row['idProveedor']}'>Eliminar</a>
                        </td>
                    </tr>";
                }
                echo "</table>";
            } else {
                echo "No hay proveedores.";
            }

        } elseif ($opcion == 'ambos') {
            $sql = "SELECT P.nombreProducto, P.cantidad, Pr.nombreProveedor, Pr.telefono, Pr.Correo 
                    FROM Productos P 
                    INNER JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor"; //consulta SQL para Prouctos
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<h2>Productos y Proveedores</h2>";
                echo "<table class='table table-bordered'>";
                echo "<tr><th>Nombre del Producto</th><th>Cantidad</th><th>Nombre del Proveedor</th><th>Teléfono</th><th>Correo</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['nombreProducto']}</td>
                        <td>{$row['cantidad']}</td>
                        <td>{$row['nombreProveedor']}</td>
                        <td>{$row['telefono']}</td>
                        <td>{$row['Correo']}</td>
                    </tr>";
                }
                
                echo "</table>";
            } else {
                echo "No hay datos.";
            }
        }
    }
    ?>
</div>
<?php  include("includes/footer.php") ?>
