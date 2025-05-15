<?php
require_once("db.php");
require_once("queries/dashboard_querie.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("includes/header.php");

// --- Variables de Paginación y Filtro ---
$selectedOption = '';
$tableHtml = '';
$paginationHtml = '';
$filterValue = '';
$recordsPerPage = 10; // Registros por página
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

// --- Procesar Selección de Tabla ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['opcion'])) {
    $selectedOption = $_POST['opcion'];
    // Resetear página a 1 cuando se cambia de opción o se aplica un nuevo filtro (no vía GET page)
    if (!isset($_GET['page'])) {
        $currentPage = 1;
    }
    if ($selectedOption == 'productos') {
        $filterValue = isset($_POST['filtro']) ? trim($_POST['filtro']) : (isset($_GET['filtro']) ? trim($_GET['filtro']) : '');
    }
} elseif (isset($_GET['opcion'])) { // Mantener opción y filtro si se navega por paginación (GET)
    $selectedOption = $_GET['opcion'];
    if ($selectedOption == 'productos') {
        $filterValue = isset($_GET['filtro']) ? trim($_GET['filtro']) : '';
    }
}


if (!empty($selectedOption)) {
    $queryResult = false;
    $tableHeaders = [];
    $actionLinks = [];

    switch ($selectedOption) {
        case 'productos':
            $queryResult = getFilteredProducts($conn, $filterValue, $currentPage, $recordsPerPage);
            $tableHeaders = ['ID', 'Nombre', 'Descripción', 'Cantidad', 'Precio Venta', 'Precio Compra', 'Proveedor', 'Categoría', 'Acciones'];
            $actionLinks = ['edit_url' => 'edit_producto.php?id=', 'delete_url' => 'delete_producto.php?id=', 'id_column' => 'idProducto'];
            break;
        case 'proveedores':
            $queryResult = getAllProviders($conn, $currentPage, $recordsPerPage);
            $tableHeaders = ['ID', 'Nombre', 'Descripción', 'Dirección', 'Teléfono', 'Correo', 'Info Adicional', 'Acciones'];
            $actionLinks = ['edit_url' => 'edit_proveedor.php?id=', 'delete_url' => 'delete_proveedor.php?id=', 'id_column' => 'idProveedor'];
            break;
        case 'ambos':
            $queryResult = getProductsWithProviders($conn, $currentPage, $recordsPerPage);
            $tableHeaders = ['Producto', 'Cantidad', 'Proveedor', 'Teléfono Prov.', 'Correo Prov.'];
            // No acciones
            break;
        case 'entradas':
            $queryResult = getAllEntries($conn, $currentPage, $recordsPerPage);
            $tableHeaders = ['ID', 'Fecha', 'Producto', 'Cant. Comprada', 'Precio Compra U.', 'Proveedor', 'Acciones'];
            $actionLinks = ['edit_url' => 'edit_entrada.php?id=', 'delete_url' => 'delete_entrada.php?id=', 'id_column' => 'idEntrada'];
            break;
        case 'ventas':
            $queryResult = getRecentSales($conn, $currentPage, $recordsPerPage);
            $tableHeaders = ['ID', 'Fecha', 'Producto', 'Cant. Vendida', 'Precio Venta T.', 'Vendedor', 'Acciones'];
            $actionLinks = ['edit_url' => 'edit_venta.php?id=', 'delete_url' => 'delete_Venta.php?id=', 'id_column' => 'idVenta'];
            break;
    }

    if ($queryResult !== false && isset($queryResult['data'])) {
        $tableData = $queryResult['data'];
        $totalRecords = $queryResult['totalRecords'];
        $totalPages = ceil($totalRecords / $recordsPerPage);

        if (!empty($tableData)) {
            $tableHtml = generateHtmlTable($tableData, $tableHeaders, $actionLinks);
            // Construir parámetros base para los enlaces de paginación
            $baseParams = ['opcion' => $selectedOption];
            if ($selectedOption == 'productos' && !empty($filterValue)) {
                $baseParams['filtro'] = $filterValue;
            }
            $paginationHtml = generatePaginationLinks($currentPage, $totalPages, $baseParams);
        } elseif ($totalRecords === 0) {
             if ($selectedOption == 'productos' && $filterValue !== '') {
                 $tableHtml = "<p class='text-center my-4'>No hay productos que coincidan con el filtro '" . htmlspecialchars($filterValue) . "'.</p>";
            } else {
                 $tableHtml = "<p class='text-center my-4'>No hay datos para mostrar para la opción '" . htmlspecialchars($selectedOption) . "'.</p>";
            }
        } else { // Hay registros totales, pero no en esta página -> podría pasar si se manipula la URL)
            $tableHtml = "<p class='text-center my-4'>No hay datos para mostrar en esta página.</p>";
            $baseParams = ['opcion' => $selectedOption];
            if ($selectedOption == 'productos' && !empty($filterValue)) {
                $baseParams['filtro'] = $filterValue;
            }
            $paginationHtml = generatePaginationLinks($currentPage, $totalPages, $baseParams);
        }
    } else {
         $tableHtml = "<p class='text-center my-4 text-danger'>Error al obtener los datos para '" . htmlspecialchars($selectedOption) . "'.</p>";
    }
}

// --- Obtener Datos para Widgets y Gráfico (Siempre se cargan) ---
$topSellingProducts = getTopSellingProducts($conn, 7);
$lowStockProducts = getLowStockProducts($conn, 7);
//$chartData = getSalesDataForChart($conn, 8);

//Rangos de diass
$days = isset($_GET['days']) ? (int)$_GET['days'] : 5;
$daysOptions = [5, 7, 10, 20];
if (!in_array($days, $daysOptions)) {
    $days = 5; // fallback
}
$chartData = getSalesDataForChart($conn, $days);


// --- Función para generar Tabla HTML (sin cambios) ---
function generateHtmlTable(array $data, array $headers, array $actions = []): string
{
    if (empty($data)) return "<p class='text-center my-4'>No hay datos para mostrar.</p>";

    $html = "<h2 class='text-center p-4'>" . htmlspecialchars(ucfirst($_REQUEST['opcion'] ?? 'Datos')) . "</h2>";
    if (isset($_REQUEST['opcion']) && $_REQUEST['opcion'] == 'ambos') $html = "<h2 class='text-center p-4'>Productos y Proveedores</h2>";
    // Para ventas, el título de "últimas 10" se elimina si se pagina sobre todas
    // if (isset($_REQUEST['opcion']) && $_REQUEST['opcion'] == 'ventas') $html .= "<h5 class='text-center p-1'>Últimas 10 ventas</h5>";


    $html .= "<div class='table-responsive'><table class='table table-striped table-hover'>";
    $html .= "<thead class='table-dark'><tr>";
    foreach ($headers as $header) {
        $html .= "<th>" . htmlspecialchars($header) . "</th>";
    }
    $html .= "</tr></thead><tbody>";
    $dataKeys = array_keys($data[0]);
    foreach ($data as $row) {
        $html .= "<tr>";
        foreach ($dataKeys as $key) {
            if (str_contains(strtolower($key), 'precio')) {
                 $precio = is_numeric($row[$key] ?? null) ? $row[$key] : 0;
                 $html .= "<td>$" . number_format($precio, 2, ',', '.') . "</td>";
            } else {
                $html .= "<td>" . htmlspecialchars($row[$key] ?? '') . "</td>";
            }
        }
        if (!empty($actions) && isset($row[$actions['id_column']])) {
            $id = $row[$actions['id_column']];
            $html .= "<td class='text-nowrap'>";
            if (isset($actions['edit_url'])) {
                $html .= "<a href='" . htmlspecialchars($actions['edit_url'] . $id) . "' class='btn btn-sm btn-warning me-1' title='Editar'><i class='fas fa-edit'></i></a>";
            }
            if (isset($actions['delete_url'])) {
                $html .= "<a href='" . htmlspecialchars($actions['delete_url'] . $id) . "' class='btn btn-sm btn-danger' title='Eliminar' onclick='return confirm(\"¿Estás seguro?\");'><i class='fas fa-trash-alt'></i></a>";
            }
            $html .= "</td>";
        } elseif (!empty($actions)) {
             $html .= "<td></td>";
        }
        $html .= "</tr>";
    }
    $html .= "</tbody></table></div>";
    return $html;
}

// --- Función para generar Enlaces de Paginación ---
function generatePaginationLinks(int $currentPage, int $totalPages, array $baseParams = [], int $linksToShow = 5): string
{
    if ($totalPages <= 1) return '';

    $paginationHtml = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center mt-4">';

    // Botón Anterior
    $prevPage = $currentPage - 1;
    $baseParams['page'] = $prevPage;
    $queryStringPrev = http_build_query($baseParams);
    $paginationHtml .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '">';
    $paginationHtml .= '<a class="page-link" href="?' . $queryStringPrev . '">Anterior</a></li>';

    // Números de página
    // Lógica para mostrar un rango de páginas si hay muchas
    $startPage = max(1, $currentPage - floor($linksToShow / 2));
    $endPage = min($totalPages, $currentPage + floor($linksToShow / 2));

    if ($startPage > 1) {
        $baseParams['page'] = 1;
        $paginationHtml .= '<li class="page-item"><a class="page-link" href="?' . http_build_query($baseParams) . '">1</a></li>';
        if ($startPage > 2) {
            $paginationHtml .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
        $baseParams['page'] = $i;
        $queryStringPage = http_build_query($baseParams);
        $paginationHtml .= '<li class="page-item ' . ($i == $currentPage ? 'active' : '') . '">';
        $paginationHtml .= '<a class="page-link" href="?' . $queryStringPage . '">' . $i . '</a></li>';
    }

     if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $paginationHtml .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $baseParams['page'] = $totalPages;
        $paginationHtml .= '<li class="page-item"><a class="page-link" href="?' . http_build_query($baseParams) . '">' . $totalPages . '</a></li>';
    }


    // Botón Siguiente
    $nextPage = $currentPage + 1;
    $baseParams['page'] = $nextPage;
    $queryStringNext = http_build_query($baseParams);
    $paginationHtml .= '<li class="page-item ' . ($currentPage >= $totalPages ? 'disabled' : '') . '">';
    $paginationHtml .= '<a class="page-link" href="?' . $queryStringNext . '">Siguiente</a></li>';

    $paginationHtml .= '</ul></nav>';
    return $paginationHtml;
}
?>

<?php if (isset($_SESSION['message'])) : ?>
    <div class="container mt-3">
        <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
<?php endif; ?>

<div class="container">
    <h2 class="mx-0 my-4 text-center">PANEL DE CONTROL</h2>
    <hr>
    <div class="d-flex justify-content-center align-items-center mb-5">

    
    <form method="GET" class="mb-3">
    <label for="days" class="form-label">Ventas de los últimos:</label>
    <select name="days" id="days" class="form-select w-auto" style="background-color: rgba(70, 180, 180, 0.3)" onchange="this.form.submit()">
        <option value="5"  <?= $days == 5 ? 'selected' : '' ?>>5 días</option>
        <option value="7"  <?= $days == 7 ? 'selected' : '' ?>>7 días</option>
        <option value="10" <?= $days == 10 ? 'selected' : '' ?>>10 días</option>
        <option value="20" <?= $days == 20 ? 'selected' : '' ?>>20 días</option>
    </select>
</form>


        <canvas class="my-4 w-100" id="miGrafico" style="max-width: 75%; height: auto;"></canvas>
    </div>
    <div class="row mb-4 justify-content-center">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="background-color: rgba(0, 216, 0, 0.1);">
                 <div class="card-body d-flex flex-column">
                    <strong class="d-block mb-2 text-primary-emphasis">Productos más vendidos</strong>
                    <ul class="list-unstyled mb-0">
                        <?php if ($topSellingProducts === false) : ?><li>Error al cargar datos.</li>
                        <?php elseif (!empty($topSellingProducts)) : foreach ($topSellingProducts as $product) : ?>
                            <li><strong><?= htmlspecialchars($product['nombreProducto']) ?></strong>: <?= htmlspecialchars($product['total_vendido']) ?> unidades</li>
                        <?php endforeach; else : ?><li>No hay productos vendidos recientemente.</li><?php endif; ?>
                    </ul>
                 </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="background-color: rgba(255, 0, 0, 0.1);">
                <div class="card-body d-flex flex-column">
                    <strong class="d-block mb-2 text-danger-emphasis">Productos con bajo stock</strong>
                     <ul class="list-unstyled mb-0">
                       <?php if ($lowStockProducts === false) : ?><li>Error al cargar datos.</li>
                       <?php elseif (!empty($lowStockProducts)) : foreach ($lowStockProducts as $product) : ?>
                            <li><strong><?= htmlspecialchars($product['nombreProducto']) ?></strong>: <?= htmlspecialchars($product['cantidad']) ?> unidades</li>
                       <?php endforeach; else : ?><li>No hay productos con bajo inventario.</li><?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <hr>

    <div class="container mt-4 mb-3">
        <form action="dashboard.php" method="POST" id="selectTableForm">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <h3 class='text-center p-2'>CONSULTAR TABLAS</h3>
                    <div class="form-group mb-3">
                        <label for="opcion" class="form-label">Selecciona una tabla para mostrar:</label>
                        <select class="form-select" id="opcion" name="opcion" onchange="this.form.submit()"> <option value="" <?= ($selectedOption == '') ? 'selected' : '' ?>>-- Seleccione --</option>
                            <option value="productos" <?= ($selectedOption == 'productos') ? 'selected' : '' ?>>Productos</option>
                            <option value="proveedores" <?= ($selectedOption == 'proveedores') ? 'selected' : '' ?>>Proveedores</option>
                            <option value="ambos" <?= ($selectedOption == 'ambos') ? 'selected' : '' ?>>Productos y Proveedores</option>
                            <option value="entradas" <?= ($selectedOption == 'entradas') ? 'selected' : '' ?>>Entradas de Productos</option>
                            <option value="ventas" <?= ($selectedOption == 'ventas') ? 'selected' : '' ?>>Ventas</option>
                        </select>
                    </div>
                    </div>
            </div>
        </form>
    </div>

    <?php if ($selectedOption == 'productos'): ?>
    <div class="container mt-2 mb-3">
         <form action="dashboard.php" method="POST" id="filterProductForm"> <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <input type="hidden" name="opcion" value="productos">
                    <div class="form-group mb-2">
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
        if (!empty($selectedOption)) {
             echo $tableHtml;
             echo $paginationHtml; // Mostrar enlaces de paginación
        }
        ?>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dias = <?php echo $days?>;
    const ctx = document.getElementById('miGrafico')?.getContext('2d');
    if (ctx) {
        const labels = <?= json_encode($chartData['labels'] ?? []) ?>;
        const data = <?= json_encode($chartData['data'] ?? []) ?>;
        const miGrafico = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ventas Últimos ' + dias + ' Días ($)',
                    data: data,
                    borderColor: 'rgba(79, 198, 198, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: { // Configuraciones para el eje X
                        ticks: {
                            color: '#000', // Cambia el color del texto del eje X a negro
                            font: {
                                size: 12,        // Cambia el tamaño de la fuente del eje X
                                weight: 'bold'  // Cambia el peso de la fuente del eje X
                            }
                        }
                    },
                    y: { // Configuraciones para el eje Y
                        beginAtZero: true,
                        ticks: {
                            color: '#000', // Cambia el color del texto del eje Y a negro
                            font: {
                                size: 12,        // Cambia el tamaño de la fuente del eje Y
                                weight: 'bold'  // Cambia el peso de la fuente del eje Y
                            },
                            callback: function(value) {
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
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += '$' + context.parsed.y.toLocaleString('es-CO', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                                return label;
                            }
                        }
                    },
                    title: {  // Configuración del título del gráfico
                        display: true,
                        text: 'Ventas de los Últimos ' + dias + ' Días',  // Puedes hacer esto dinámico si `dias` es una variable de PHP
                        color: '#000',  // Color del título
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: { //Configuración de la leyenda
                       labels: {
                           color: '#000',  // Color de la leyenda
                           font: {
                                size: 12,
                                weight: 'normal'
                            }
                       }
                    }
                }
            }
        });
    } else {
        console.error("Canvas 'miGrafico' no encontrado.");
    }
</script>

<?php include("includes/footer.php"); ?>