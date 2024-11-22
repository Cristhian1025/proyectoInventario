<?php  include("db.php");?>
  
<?php include("includes/header.php") ;

if (isset($_SESSION['message'])) { ?>
    <div class="mx-4 my-4 col-lg-4 alert alert-<?= $_SESSION['message_type']?> alert-dismissible fade show" role="alert">
      <?= $_SESSION['message']?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <?php session_unset(); } ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <h2 class="mx-4 my-4 text-center">    PANEL DE CONTROL</h2>
    <hr>
    <br>

    <div class="d-flex justify-content-center align-items-center mb-5">
        <canvas id="miGrafico" style="max-width: 80%; height: auto;"></canvas>
    </div>

    <div>
    

    <div class= "container d-flex justify-content-center align-items-center" style="height: 50vh;">
        
    <div class="row mb-2">
    <div class="col-md-6">
      <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
        <div class="col p-4 d-flex flex-column position-static">
          <strong class="d-inline-block mb-2 text-primary-emphasis">Demandados</strong>
          <h3 class="mb-0">Subtitulo 1</h3>

          

          <div class="mb-1 text-body-secondary">Textaco1</div>
          <p class="card-text mb-auto">Parrafo1.</p>
        </div>
        
      </div>
    </div>
    
    <div class="col-md-6">
      <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
        <div class=" col p-4 d-flex flex-column position-static">

        

                    
          <strong class="d-inline-block mb-2 text-success-emphasis">Bajo Stock</strong>

          <ul>
                <?php
                // Consulta para los 7 productos con menor cantidad
                $sql = "SELECT nombreProducto, cantidad FROM Productos ORDER BY cantidad ASC LIMIT 7";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<li><strong>{$row['nombreProducto']}</strong>: {$row['cantidad']} unidades</li>";
                    }
                } else {
                    echo "<li>No hay productos con bajo inventario.</li>";
                }
                ?>
            </ul>
    
          <div class="mb-1 text-body-secondary">textico 11</div>
          <p class="mb-auto">adsf adf adsf asdfffsaf.</p>
          
        </div>
        
      </div>
    </div>
  </div>




    </div>
    </div>      

    <br>
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
        <button type="submit" class="btn btn-primary my-4 mx-4">Mostrar</button>
    </form>
</div>
<hr>
<div class="container mt-4">


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
    echo "<h2 class='text-center p-4'>Productos</h2>";
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
                echo "<h2 class='text-center p-4'>Proveedores</h2>";
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
                echo "<h2 class='text-center p-4'>Productos y Proveedores</h2>";
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
                echo "<h2 class='text-center p-4'>Entradas</h2>";
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
                echo "<h2 class='text-center p-4'>Ventas</h2>";
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

    $fechas = [];
for ($i = 4; $i >= 0; $i--) {
    $fechas[] = date('Y-m-d', strtotime("-$i days"));
}

// Convierte el arreglo de fechas en una cadena para la consulta SQL
$fechasString = "'" . implode("', '", $fechas) . "'";

// Consulta SQL con suma de ventas por fecha
$sql = "SELECT DATE(V.fechaVenta) AS fecha, SUM(V.precioVentaTotal) AS total_ventas
        FROM ventas V
        WHERE DATE(V.fechaVenta) IN ($fechasString)
        GROUP BY DATE(V.fechaVenta)
        ORDER BY DATE(V.fechaVenta) ASC";
$result = $conn->query($sql);

// Procesar resultados
$ventas = array_fill_keys($fechas, 0); // Inicializar ventas en 0 para todas las fechas
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $ventas[$row['fecha']] = $row['total_ventas'];
    }
}

// Convertir datos para el gráfico
$ventas = array_values($ventas);

    ?>
</div>
<script>
    //Configuración del gráfico
    const ctx = document.getElementById('miGrafico').getContext('2d');

    const labels = <?= json_encode($fechas) ?>;
    const data = <?= json_encode($ventas) ?>;

    const miGrafico = new Chart(ctx, {
      type: 'line', // Tipo de gráfico
      data: {
        labels: labels, // Fechas en el eje X
        datasets: [{
          label: 'Ultimos 5 dias',

          data: data, // Valores de ventas
          borderColor: 'rgba(75, 192, 192, 1)', // Color de la línea
          backgroundColor: 'rgba(75, 192, 192, 0.2)', // Color de fondo debajo de la línea
          borderWidth: 2,
          fill: true // Relleno debajo de la línea
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>

<?php  include("includes/footer.php") ?>
