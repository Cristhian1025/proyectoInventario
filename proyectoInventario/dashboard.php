<?php 
    include("db.php");
  
  
include("includes/header.php") ?>

<nav class ="navbar navbar-dark bg-dark">        
        <a href="dashboard.php" class="caja_nav" style="background-color: blue; color: aliceblue;">Inicio</a>
        <!-- <a href="#" class="caja_nav">Empleados</a> -->
        <a href="productos.php" class="caja_nav">Productos</a>
        <a href="proveedores.php" class="caja_nav">proveedores</a>
    </nav>

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
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $opcion = $_POST['opcion'];

        if ($opcion == 'productos') {
            $sql = "SELECT * FROM Productos";
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
            $sql = "SELECT * FROM Proveedores";
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
                    JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor";
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
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
    
