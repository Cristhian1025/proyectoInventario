<?php  include("db.php");?>
  
<?php include("includes/header.php") ;
?>
    

    <h1>    PANEL DE CONTROL</h1>
    <div class="container mt-4" >
    <form action="dashboard.php" method="POST">
        <div class="form-group">
            <label for="opcion">Selecciona una opción:</label>
            <select class="form-control" id="opcion" name="opcion">
                <option value="productos">Productos</option>
                <option value="proveedores">Proveedores</option>
                <option value="ambos">Productos y Proveedores</option>
                <option value="entradas">Entradas de Productos</option>
                <option value="ventas">Ventas</option>
            </select> 
        </div>
        <button type="submit" class="btn btn-primary">Mostrar</button>
    </form>
</div>

<div class="container mt-4">

<!-- EL CODIGO INCRUSTADO DE PHP -->

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $opcion = $_POST['opcion'];

        if ($opcion == 'productos') {
            // Consulta SQL para Productos con JOIN en Proveedores y Categorías
        $sql = "SELECT P.idProducto, P.nombreProducto, P.descripcionProducto, P.cantidad, P.precioVenta, P.precioCompra, Pr.nombreProveedor, C.nombreCategoria
        FROM Productos P
        LEFT JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor
        LEFT JOIN Categorias C ON P.CategoriaId = C.idCategoria";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Productos</h2>";
    echo "<table class='table table-striped table-hover table-responsive'>";
    echo "<tr class='table-dark text-white'><th>ID</th><th>Nombre</th><th>Descripción</th><th>Cantidad</th><th>Precio Venta</th><th>Precio Compra</th><th>Proveedor</th><th>Categoría</th><th>Acciones</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['idProducto']}</td>
            <td>{$row['nombreProducto']}</td>
            <td>{$row['descripcionProducto']}</td>
            <td>{$row['cantidad']}</td>
            <td>{$row['precioVenta']}</td>
            <td>{$row['precioCompra']}</td>
            <td>{$row['nombreProveedor']}</td>
            <td>{$row['nombreCategoria']}</td>
            <td>
                <a href='edit_producto.php?id={$row['idProducto']}'>Editar</a> |
                <a style='color:red;' href='delete_producto.php?id={$row['idProducto']}'>Eliminar</a>
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
                echo "<table class='table table-striped table-hover table-responsive''>";
                echo "<tr class='table-dark text-white'><th>ID</th><th>Nombre</th><th>Descripción</th><th>Dirección</th><th>Teléfono</th><th>Correo</th><th>Información Adicional</th><th>Acciones</th></tr>";
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
                    INNER JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor"; //consulta SQL para Unir Prouctos con proveedores
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<h2>Productos y Proveedores</h2>";
                echo "<table class='table table-striped table-hover table-responsive'>";
                echo "<tr class='table-dark text-white'><th>Nombre del Producto</th><th>Cantidad</th><th>Nombre del Proveedor</th><th>Teléfono</th><th>Correo</th></tr>";
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
        }elseif ($opcion == 'entradas') {
            $sql = "SELECT E.idEntrada, E.fechaEntrada, P.nombreProducto, E.cantidadComprada, E.precioCompraUnidad, Pr.nombreProveedor
                    FROM EntradaProductos E
                    LEFT JOIN Productos P ON E.productoId = P.idProducto
                    LEFT JOIN Proveedores Pr ON E.proveedorId = Pr.idProveedor";
            $result = $conn->query($sql);
    
            if ($result->num_rows > 0) {
                echo "<h2>Entradas</h2>";
                echo "<table class='table table-striped table-hover table-responsive'>";
                echo "<tr class='table-dark text-white'><th>ID Entrada</th><th>Fecha</th><th>Producto</th><th>Cantidad Comprada</th><th>Precio Compra Unidad</th><th>Proveedor</th><th>Acciones</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['idEntrada']}</td>
                        <td>{$row['fechaEntrada']}</td>
                        <td>{$row['nombreProducto']}</td>
                        <td>{$row['cantidadComprada']}</td>
                        <td>{$row['precioCompraUnidad']}</td>
                        <td>{$row['nombreProveedor']}</td>
                        <td>
                            <a href='edit_entrada.php?id={$row['idEntrada']}'>Editar</a> |
                            <a style='color:red;' href='delete_entrada.php?id={$row['idEntrada']}'>Eliminar</a>
                        </td>
                    </tr>";
                }
                echo "</table>";
            } else {
                echo "No hay entradas.";
            }


        }elseif($opcion == 'ventas') {

            $sql = "SELECT V.idVenta, V.fechaVenta, P.nombreProducto, V.cantidadVenta, V.precioVentaTotal, Vd.nombreCompleto
                    FROM ventas V
                    LEFT JOIN Productos P ON V.productoId = P.idProducto
                    LEFT JOIN usuario Vd ON V.vendedorId = Vd.idUsuario";
            $result = $conn->query($sql);
    
            if ($result->num_rows > 0) {
                echo "<h2>Ventas</h2>";
                echo "<table class='table table-striped table-hover table-responsive'>";
                echo "<tr class='table-dark text-white'><th>ID Venta</th><th>Fecha</th><th>Producto</th><th>Cantidad Vendida</th><th>Precio Venta</th><th>Usuario</th><th>Acciones</th></tr>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['idVenta']}</td>
                        <td>{$row['fechaVenta']}</td>
                        <td>{$row['nombreProducto']}</td>
                        <td>{$row['cantidadVenta']}</td>
                        <td>{$row['precioVentaTotal']}</td>
                        <td>{$row['nombreCompleto']}</td>
                        <td>
                            <a href='edit_venta.php?id={$row['idVenta']}'>Editar</a> |
                            <a style='color:red;' href='delete_Venta.php?id={$row['idVenta']}'>Eliminar</a>
                        </td>
                    </tr>";
                }
                echo "</table>";
            } else {
                echo "No hay entradas.";
            }
        }
    }
    ?>
</div>
<?php  include("includes/footer.php") ?>
