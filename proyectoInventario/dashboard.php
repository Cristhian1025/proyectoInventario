<?php
// Incluir conexión y funciones de query (ajusta la ruta si es necesario)
require_once("db.php");
require_once("queries/dashboard_querie.php");
// require_once("queries/product_queries.php"); // Si separas más las queries
// require_once("queries/provider_queries.php"); // etc.

// Iniciar sesión si no está iniciada (importante para los mensajes flash)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir cabecera HTML
include("includes/header.php");

// --- Variables para almacenar datos y estado ---
$selectedOption = '';
$tableHtml = ''; // Variable para almacenar el HTML de la tabla a mostrar
$filterValue = ''; // Para mantener el valor del filtro de productos

// --- Procesar Selección de Tabla (si se envió el formulario principal) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['opcion'])) {
    $selectedOption = $_POST['opcion'];
    $tableData = false;
    $tableHeaders = [];
    $actionLinks = []; // Para configurar enlaces Editar/Eliminar

    // Si la opción es productos, obtener el valor del filtro (si existe)
    if ($selectedOption == 'productos') {
        $filterValue = isset($_POST['filtro']) ? $_POST['filtro'] : '';
    }

    // Determinar qué tabla mostrar y obtener datos
    switch ($selectedOption) {
        case 'productos':
            $tableData = getFilteredProducts($conn, $filterValue); // Usar $filterValue
            $tableHeaders = ['ID', 'Nombre', 'Descripción', 'Cantidad', 'Precio Venta', 'Precio Compra', 'Proveedor', 'Categoría', 'Acciones'];
            $actionLinks = [
                'edit_url' => 'edit_producto.php?id=',
                'delete_url' => 'delete_producto.php?id=',
                'id_column' => 'idProducto'
            ];
            break; // No generamos el form del filtro aquí

        case 'proveedores':
            $tableData = getAllProviders($conn);
            $tableHeaders = ['ID', 'Nombre', 'Descripción', 'Dirección', 'Teléfono', 'Correo', 'Info Adicional', 'Acciones'];
             $actionLinks = [
                'edit_url' => 'edit_proveedor.php?id=',
                'delete_url' => 'delete_proveedor.php?id=',
                'id_column' => 'idProveedor'
            ];
            break;

        case 'ambos':
            $tableData = getProductsWithProviders($conn);
            $tableHeaders = ['Producto', 'Cantidad', 'Proveedor', 'Teléfono Prov.', 'Correo Prov.'];
            break;

        case 'entradas':
            $tableData = getAllEntries($conn);
            $tableHeaders = ['ID', 'Fecha', 'Producto', 'Cant. Comprada', 'Precio Compra U.', 'Proveedor', 'Acciones'];
            $actionLinks = [
                'edit_url' => 'edit_entrada.php?id=',
                'delete_url' => 'delete_entrada.php?id=',
                'id_column' => 'idEntrada'
            ];
            break;

        case 'ventas':
            $tableData = getRecentSales($conn, 10);
            $tableHeaders = ['ID', 'Fecha', 'Producto', 'Cant. Vendida', 'Precio Venta T.', 'Vendedor', 'Acciones'];
             $actionLinks = [
                'edit_url' => 'edit_venta.php?id=',
                'delete_url' => 'delete_Venta.php?id=',
                'id_column' => 'idVenta'
            ];
            break;
    }

    // Generar HTML de la tabla si hay datos
    if ($tableData !== false && !empty($tableData)) {
        $tableHtml = generateHtmlTable($tableData, $tableHeaders, $actionLinks);
    } elseif ($tableData !== false && empty($tableData)) {
        // Mensaje específico si el filtro no arrojó resultados
        if ($selectedOption == 'productos' && $filterValue !== '') {
             $tableHtml = "<p class='text-center my-4'>No hay productos que coincidan con el filtro '" . htmlspecialchars($filterValue) . "'.</p>";
        } else {
             $tableHtml = "<p class='text-center my-4'>No hay datos para mostrar para la opción '" . htmlspecialchars($selectedOption) . "'.</p>";
        }
    } else {
         $tableHtml = "<p class='text-center my-4 text-danger'>Error al obtener los datos para '" . htmlspecialchars($selectedOption) . "'.</p>";
    }
}

// --- Obtener Datos para Widgets y Gráfico (Siempre se cargan) ---
$topSellingProducts = getTopSellingProducts($conn, 7);
$lowStockProducts = getLowStockProducts($conn, 7);
$chartData = getSalesDataForChart($conn, 5);

/**
 * Función auxiliar para generar una tabla HTML (sin cambios respecto a la versión anterior)
 */
function generateHtmlTable(array $data, array $headers, array $actions = []): string
{
     if (empty($data)) return '';

    $html = "<h2 class='text-center p-4'>" . htmlspecialchars(ucfirst($_POST['opcion'] ?? 'Datos')) . "</h2>"; // Usar la opción actual para el título
     // Ajuste para título específico de 'ambos' y 'ventas'
    if (isset($_POST['opcion']) && $_POST['opcion'] == 'ambos') $html = "<h2 class='text-center p-4'>Productos y Proveedores</h2>";
    if (isset($_POST['opcion']) && $_POST['opcion'] == 'ventas') $html .= "<h5 class='text-center p-1'>Últimas 10 ventas</h5>";


    $html .= "<div class='table-responsive'><table class='table table-striped table-hover'>"; // table-responsive para tablas anchas
    $html .= "<thead class='table-dark'><tr>"; // thead con clase oscura

    // Generar encabezados
    foreach ($headers as $header) {
        $html .= "<th>" . htmlspecialchars($header) . "</th>";
    }
    $html .= "</tr></thead><tbody>";

    // Generar filas de datos
    $dataKeys = array_keys($data[0]); // Obtener las claves del primer elemento para el orden
    foreach ($data as $row) {
        $html .= "<tr>";
        foreach ($dataKeys as $key) {
             // Formato especial para precios (si existen esas columnas)
            if (str_contains(strtolower($key), 'precio')) {
                 // Manejar posible valor null o no numérico
                 $precio = is_numeric($row[$key] ?? null) ? $row[$key] : 0;
                 $html .= "<td>$" . number_format($precio, 2, ',', '.') . "</td>";
            } else {
                $html .= "<td>" . htmlspecialchars($row[$key] ?? '') . "</td>";
            }
        }

        // Añadir acciones si están configuradas
        if (!empty($actions) && isset($row[$actions['id_column']])) {
            $id = $row[$actions['id_column']];
            $html .= "<td class='text-nowrap'>"; // Evitar que los botones se partan
            if (isset($actions['edit_url'])) {
                $html .= "<a href='" . htmlspecialchars($actions['edit_url'] . $id) . "' class='btn btn-sm btn-warning me-1' title='Editar'><i class='fas fa-edit'></i></a>"; // Icono Editar
            }
            if (isset($actions['delete_url'])) {
                 // Añadir confirmación JS al eliminar
                $html .= "<a href='" . htmlspecialchars($actions['delete_url'] . $id) . "' class='btn btn-sm btn-danger' title='Eliminar' onclick='return confirm(\"¿Estás seguro de que quieres eliminar este registro?\");'><i class='fas fa-trash-alt'></i></a>"; // Icono Eliminar
            }
            $html .= "</td>";
        } elseif (!empty($actions)) { // Si se esperan acciones pero no hay columna ID
             $html .= "<td></td>";
        }

        $html .= "</tr>";
    }

    $html .= "</tbody></table></div>";
    return $html;
}

?>

<?php if (isset($_SESSION['message'])) : ?>
    <div class="container mt-3">
        <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    ?>
<?php endif; ?>


<div class="container">
    <h2 class="mx-0 my-4 text-center">PANEL DE CONTROL</h2>
    <hr>

    <div class="d-flex justify-content-center align-items-center mb-5">
        <canvas class="my-4 w-100" id="miGrafico" style="max-width: 75%; height: auto;"></canvas>
    </div>

    <div class="row mb-4 justify-content-center">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="background-color: rgba(0, 216, 0, 0.1);">
                 <div class="card-body d-flex flex-column">
                    <strong class="d-block mb-2 text-primary-emphasis">Productos más vendidos</strong>
                    <ul class="list-unstyled mb-0">
                        <?php if ($topSellingProducts === false) : ?>
                            <li>Error al cargar datos.</li>
                        <?php elseif (!empty($topSellingProducts)) : ?>
                            <?php foreach ($topSellingProducts as $product) : ?>
                                <li>
                                    <strong><?= htmlspecialchars($product['nombreProducto']) ?></strong>:
                                    <?= htmlspecialchars($product['total_vendido']) ?> unidades
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <li>No hay productos vendidos recientemente.</li>
                        <?php endif; ?>
                    </ul>
                 </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="background-color: rgba(255, 0, 0, 0.1);">
                <div class="card-body d-flex flex-column">
                    <strong class="d-block mb-2 text-danger-emphasis">Productos con bajo stock</strong>
                     <ul class="list-unstyled mb-0">
                       <?php if ($lowStockProducts === false) : ?>
                            <li>Error al cargar datos.</li>
                        <?php elseif (!empty($lowStockProducts)) : ?>
                            <?php foreach ($lowStockProducts as $product) : ?>
                                <li>
                                    <strong><?= htmlspecialchars($product['nombreProducto']) ?></strong>:
                                    <?= htmlspecialchars($product['cantidad']) ?> unidades
                                 </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <li>No hay productos con bajo inventario.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div> <hr>

    <div class="container mt-4 mb-3">
        <form action="dashboard.php" method="POST" id="selectTableForm">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <h3 class='text-center p-2'>CONSULTAR TABLAS</h3>
                    <div class="form-group mb-3">
                        <label for="opcion" class="form-label">Selecciona una tabla para mostrar:</label>
                        <select class="form-select" id="opcion" name="opcion">
                            <option value="" <?= ($selectedOption == '') ? 'selected' : '' ?>>-- Seleccione --</option> <option value="productos" <?= ($selectedOption == 'productos') ? 'selected' : '' ?>>Productos</option>
                            <option value="proveedores" <?= ($selectedOption == 'proveedores') ? 'selected' : '' ?>>Proveedores</option>
                            <option value="ambos" <?= ($selectedOption == 'ambos') ? 'selected' : '' ?>>Productos y Proveedores</option>
                            <option value="entradas" <?= ($selectedOption == 'entradas') ? 'selected' : '' ?>>Entradas de Productos</option>
                            <option value="ventas" <?= ($selectedOption == 'ventas') ? 'selected' : '' ?>>Ventas</option>
                        </select>
                    </div>
                     <div class="text-center">
                        <button type="submit" class="btn btn-primary">Mostrar Tabla</button>
                     </div>
                </div>
            </div>
        </form>
    </div>

    <?php if ($selectedOption == 'productos'): ?>
    <div class="container mt-2 mb-3">
         <form action="dashboard.php" method="POST" id="filterProductForm">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <input type="hidden" name="opcion" value="productos"> <div class="form-group mb-2">
                        <label for="filtro" class="form-label">Filtrar Productos por (Nombre, proveedor, categoria):</label>
                        <input type="text" name="filtro" id="filtro" class="form-control" placeholder="Ingresa texto para filtrar" value="<?= htmlspecialchars($filterValue) ?>">
                    </div>
                    <div class="text-center">
                         <button type="submit" class="btn btn-secondary">Aplicar Filtro</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>


    <div class="container mt-2">
        <?php
        // Mostrar el HTML de la tabla generado previamente si $selectedOption no está vacío
        if (!empty($selectedOption)) {
             echo $tableHtml;
        }
        ?>
    </div>

</div> <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('miGrafico')?.getContext('2d');
    if (ctx) {
        const labels = <?= json_encode($chartData['labels'] ?? []) ?>;
        const data = <?= json_encode($chartData['data'] ?? []) ?>;

        const miGrafico = new Chart(ctx, {
            type: 'line',
            data: { /* ... igual que antes ... */
                labels: labels,
                datasets: [{
                    label: 'Ventas Últimos 5 Días ($)',
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: { /* ... igual que antes ... */
                 responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return '$' + value.toLocaleString('es-CO');
                            }
                        }
                    }
                },
                 plugins: {
                    tooltip: {
                         callbacks: {
                             label: function(context) {
                                 let label = context.dataset.label || '';
                                 if (label) { label += ': '; }
                                 if (context.parsed.y !== null) {
                                     label += '$' + context.parsed.y.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                 }
                                 return label;
                             }
                         }
                    }
                 }
            }
        });
    } else {
        console.error("No se pudo encontrar el contexto del canvas 'miGrafico'.");
    }
</script>

<?php include("includes/footer.php") // Incluir pie de página HTML ?>