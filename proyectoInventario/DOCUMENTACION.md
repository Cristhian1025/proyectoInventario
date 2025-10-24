# Documentación Técnica del Proyecto de Inventario

Este documento contiene el código fuente de los principales archivos PHP del proyecto, extraído directamente para su revisión. Los archivos de librerías de terceros (como FPDF) y de configuración de base de datos han sido omitidos.

---

## Archivo: `code.php`

```php
<?php
/**
 * code.php
 *
 * Archivo de ejemplo que realiza una verificación muy básica de credenciales
 * enviadas por un formulario. Añadido encabezado de documentación en español.
 * No cambia la lógica original.
 */
/**
 * code.php
 *
 * Archivo de ejemplo que realiza una verificación muy básica de credenciales
 * enviadas por un formulario. Este archivo está documentado en español.
 *
 * Explicación breve:
 * - Define las credenciales esperadas ($usuarioR, $contraseñaR).
 * - Si la petición es POST y se envían 'email' y 'password', compara los valores
 *   con las credenciales esperadas.
 * - Si las credenciales coinciden, redirige al usuario (ejemplo: Google).
 * - Si no coinciden o faltan datos, muestra mensajes de error.
 *
 * Seguridad: Este código usa credenciales en texto plano y está pensado solo
 * como ejemplo. No usar en producción. En entornos reales:
 * - Almacenar contraseñas con hashing (password_hash / password_verify).
 * - Usar HTTPS y controles de sesión.
 * - Validar y sanitizar todas las entradas del usuario.
 */

// Credenciales esperadas (ejemplo)
$usuarioR = "usuario";
$contraseñaR = "contraseña";

/**
 * Autentica al usuario comparando las credenciales proporcionadas con las esperadas.
 *
 * @param string $email Correo o nombre de usuario enviado por el formulario.
 * @param string $password Contraseña enviada por el formulario.
 * @param string $expectedUser Usuario esperado (valor estático o desde configuración).
 * @param string $expectedPass Contraseña esperada (valor estático o desde configuración).
 * @return bool Devuelve true si las credenciales coinciden exactamente, false en caso contrario.
 *
 * Nota: La comparación es estricta (===). En producción se debe usar un método
 * seguro de verificación (por ejemplo, password_verify para contraseñas hasheadas).
 */
function autenticar_usuario($email, $password, $expectedUser, $expectedPass)
{
    return ($email === $expectedUser && $password === $expectedPass);
}


// Manejo de la petición
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica si se recibieron los campos necesarios desde el formulario
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Obtiene los valores enviados por el formulario
        $usuario = $_POST['email'];
        $contraseña = $_POST['password'];

        // Llama a la función de autenticación (con docstring en español)
        if (autenticar_usuario($usuario, $contraseña, $usuarioR, $contraseñaR)) {
            // Credenciales correctas: redirige al destino (ejemplo)
            header("Location: https://www.google.com");
            exit;
        } else {
            // Credenciales incorrectas: muestra mensaje de error
            echo "Los datos ingresados son incorrectos.";
        }
    } else {
        // Faltan campos en la petición POST
        echo "Ingresar el nombre de usuario y la contraseña.";
    }
} else {
    // No se recibió una petición POST
    echo "Acceso no autorizado.";
}

?>
```

---

## Archivo: `dashboard.php`

```php
<?php
/**
 * dashboard.php
 *
 * Panel de control principal del sistema de inventario.
 * Encabezado agregado en español. No se modifica la lógica existente.
 */
/**
 * dashboard.php
 *
 * Panel de control principal del sistema de inventario.
 *
 * Este archivo muestra widgets (productos más vendidos, productos con bajo stock),
 * un gráfico de ventas en los últimos N días y permite consultar tablas (productos,
 * proveedores, entradas y ventas) con paginación y filtrado básico.
 *
 * Documentación en español añadida: comentarios explicativos y PHPDoc en las
 * funciones definidas en este archivo.
 *
 * Notas de seguridad y buenas prácticas:
 * - Validar y sanitizar todas las entradas (se usan htmlspecialchars en las salidas).
 * - Evitar lógica de negocio compleja en las vistas; separar en capas.
 */

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
            $tableHeaders = ['ID Venta', 'Fecha', 'Vendedor', 'Total Venta', 'Acciones'];
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
/**
 * Genera una tabla HTML a partir de un array de datos.
 *
 * Este helper construye la estructura HTML de la tabla, formatea campos que contienen
 * la palabra 'precio' como valores monetarios y añade botones de acción cuando se
 * proporcionan URLs de edición/ eliminación en $actions.
 *
 * @param array $data Matriz de filas (cada fila es un array asociativo con claves -> columnas).
 * @param array $headers Lista de cabeceras a mostrar en la tabla (orden de columnas deseado).
 * @param array $actions Opcional. Array con claves 'edit_url', 'delete_url' y 'id_column' para crear botones de acción.
 * @return string HTML seguro y listo para imprimir (usar echo) con la tabla generada.
 */
function generateHtmlTable(array $data, array $headers, array $actions = []): string
{
    // Si no hay datos, devolvemos un mensaje estándar
    if (empty($data)) return "<p class='text-center my-4'>No hay datos para mostrar.</p>";

    // Título principal basado en la opción solicitada (productos, proveedores, etc.)
    $html = "<h2 class='text-center p-4'>" . htmlspecialchars(ucfirst($_REQUEST['opcion'] ?? 'Datos')) . "</h2>";
    if (isset($_REQUEST['opcion']) && $_REQUEST['opcion'] == 'ambos') $html = "<h2 class='text-center p-4'>Productos y Proveedores</h2>";

    // Inicio de la tabla
    $html .= "<div class='table-responsive'><table class='table table-striped table-hover'>";
    $html .= "<thead class='table-dark'><tr>";
    foreach ($headers as $header) {
        // Escapamos las cabeceras para evitar inyección de HTML
        $html .= "<th>" . htmlspecialchars($header) . "</th>";
    }
    $html .= "</tr></thead><tbody>";

    // Caso especial para la tabla de ventas (formato de fecha y monto)
    if (isset($_REQUEST['opcion']) && $_REQUEST['opcion'] == 'ventas') {
        foreach ($data as $row) {
            $html .= "<tr>";
            $html .= "<td>" . htmlspecialchars($row['idVenta'] ?? '') . "</td>";
            // Formateamos la fecha al formato DD/MM/YYYY
            $html .= "<td>" . htmlspecialchars(date("d/m/Y", strtotime($row['fechaVenta']))) . "</td>";
            $html .= "<td>" . htmlspecialchars($row['nombreCompleto'] ?? '') . "</td>";
            $precio = is_numeric($row['totalVenta'] ?? null) ? $row['totalVenta'] : 0;
            $html .= "<td>$" . number_format($precio, 2, ',', '.') . "</td>";

            // Celda de acciones para ventas: muestra un enlace a la factura en lugar de editar/eliminar
            if (!empty($actions) && isset($row[$actions['id_column']])) {
                $id = $row[$actions['id_column']];
                $html .= "<td class='text-nowrap'>";
                $html .= "<a href='generar_factura.php?id_venta=" . htmlspecialchars($id) . "' class='btn btn-sm btn-info' target='_blank' title='Ver Factura'><i class='fas fa-file-pdf'></i> Ver Factura</a>";
                $html .= "</td>";
            } else {
                $html .= "<td></td>";
            }
            $html .= "</tr>";
        }
    } else {
        // Comportamiento general para otras tablas: generamos filas basadas en las claves del primer registro
        $dataKeys = array_keys($data[0]);
        foreach ($data as $row) {
            $html .= "<tr>";
            foreach ($dataKeys as $key) {
                // Si la clave suena a precio, aplicamos formato monetario
                if (str_contains(strtolower($key), 'precio')) {
                     $precio = is_numeric($row[$key] ?? null) ? $row[$key] : 0;
                     $html .= "<td>$" . number_format($precio, 2, ',', '.') . "</td>";
                } else {
                    // Escapamos cualquier valor mostrado
                    $html .= "<td>" . htmlspecialchars($row[$key] ?? '') . "</td>";
                }
            }

            // Si se proporcionaron URLs de acción, añadimos los botones de editar/eliminar
            if (!empty($actions) && isset($row[$actions['id_column']])) {
                $id = $row[$actions['id_column']];
                $html .= "<td class='text-nowrap'>";
                if (isset($actions['edit_url'])) {
                    $html .= "<a href='" . htmlspecialchars($actions['edit_url'] . $id) . "' class='btn btn-sm btn-warning me-1' title='Editar'><i class='fas fa-edit'></i></a>";
                }
                if (isset($actions['delete_url'])) {
                    // Escapamos las comillas internas para evitar romper la cadena PHP
                    $html .= "<a href='" . htmlspecialchars($actions['delete_url'] . $id) . "' class='btn btn-sm btn-danger' title='Eliminar' onclick='return confirm("¿Estás seguro?");'><i class='fas fa-trash-alt'></i></a>";
                }
                $html .= "</td>";
            } elseif (!empty($actions)) {
                 $html .= "<td></td>";
            }
            $html .= "</tr>";
        }
    }
    $html .= "</tbody></table></div>";
    return $html;
}

// --- Función para generar Enlaces de Paginación ---
/**
 * Genera el HTML para los enlaces de paginación.
 *
 * Crea enlaces "Anterior", varios números de página (con puntos suspensivos cuando procede)
 * y "Siguiente". Utiliza $baseParams para conservar parámetros GET (como 'opcion' y 'filtro').
 *
 * @param int $currentPage Página actual (1-based).
 * @param int $totalPages Total de páginas calculadas.
 * @param array $baseParams Parámetros base que se incluirán en cada enlace (se añade 'page').
 * @param int $linksToShow Número aproximado de enlaces a mostrar alrededor de la página actual.
 * @return string HTML con la barra de paginación o cadena vacía si no es necesario paginar.
 */
function generatePaginationLinks(int $currentPage, int $totalPages, array $baseParams = [], int $linksToShow = 5): string
{
    // No mostramos paginación si solo hay una página o menos
    if ($totalPages <= 1) return '';

    $paginationHtml = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center mt-4">';

    // Botón Anterior (deshabilitado si estamos en la primera página)
    $prevPage = $currentPage - 1;
    $baseParams['page'] = $prevPage;
    $queryStringPrev = http_build_query($baseParams);
    $paginationHtml .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '">';
    $paginationHtml .= '<a class="page-link" href="?' . $queryStringPrev . '">Anterior</a></li>';

    // Calculamos rango de páginas a mostrar alrededor de la página actual
    $startPage = max(1, $currentPage - floor($linksToShow / 2));
    $endPage = min($totalPages, $currentPage + floor($linksToShow / 2));

    // Si el rango comienza después de 1, añadimos el enlace a la primera página y puntos suspensivos
    if ($startPage > 1) {
        $baseParams['page'] = 1;
        $paginationHtml .= '<li class="page-item"><a class="page-link" href="?' . http_build_query($baseParams) . '">1</a></li>';
        if ($startPage > 2) {
            $paginationHtml .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Enlaces numerados dentro del rango calculado
    for ($i = $startPage; $i <= $endPage; $i++) {
        $baseParams['page'] = $i;
        $queryStringPage = http_build_query($baseParams);
        $paginationHtml .= '<li class="page-item ' . ($i == $currentPage ? 'active' : '') . '">';
        $paginationHtml .= '<a class="page-link" href="?' . $queryStringPage . '">' . $i . '</a></li>';
    }

    // Si el rango no llega hasta la última página, mostramos puntos y el enlace a la última
     if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $paginationHtml .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $baseParams['page'] = $totalPages;
        $paginationHtml .= '<li class="page-item"><a class="page-link" href="?' . http_build_query($baseParams) . '">' . $totalPages . '</a></li>';
    }


    // Botón Siguiente (deshabilitado si estamos en la última página)
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
```

---

## Archivo: `db.php`

```php
<?php
/**
 * db.php
 *
 * Archivo de conexión a la base de datos MySQL.
 * 
 * Este archivo establece la conexión con la base de datos del sistema de inventario.
 * Incluye una verificación de sesión para evitar advertencias si la sesión
 * ya fue iniciada en otro archivo.
 *
 * ⚠️ Recomendación:
 * Mantén las credenciales (usuario, contraseña, host y nombre de base de datos)
 * en un archivo seguro fuera del repositorio público.
 */

// Iniciar la sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Parámetros de conexión
$host = 'localhost';
$user = 'root';
$password = '0000'; // ← tu contraseña
$database = 'inventario';

// Establecer conexión con MySQL
$conn = mysqli_connect($host, $user, $password, $database);

// Verificar conexión
if (!$conn) {
    die("❌ Conexión fallida: " . mysqli_connect_error());
} else {
    // Puedes dejar esta línea solo durante pruebas
    // echo "✅ Conexión exitosa a la base de datos.";
}
?>
```

---

## Archivo: `delete_entrada.php`

```php
<?php
/**
 * delete_entrada.php
 *
 * Script que elimina una entrada de productos y ajusta el stock restando
 * la cantidad correspondiente. Se añadió encabezado de documentación en español.
 */

include("db.php");

if (isset($_GET['id'])) {
    $idEntrada = $_GET['id'];
    // Obtener la cantidad comprada y el producto ID antes de eliminar
    $query = "SELECT cantidadComprada, productoId FROM EntradaProductos WHERE idEntrada = $idEntrada";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    $cantidadComprada = $row['cantidadComprada'];
    $productoId = $row['productoId'];

    // Restar la cantidad comprada del producto
    $query = "UPDATE Productos SET cantidad = cantidad - $cantidadComprada WHERE idProducto = $productoId";
    mysqli_query($conn, $query);

    // Eliminar la entrada de producto
    $query = "DELETE FROM EntradaProductos WHERE idEntrada = $idEntrada";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query Failed.");
    }

    $_SESSION['message'] = 'Entrada eliminada correctamente';
    $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
}
?>
```

---

## Archivo: `delete_producto.php`

```php
<?php
/**
 * delete_producto.php
 *
 * Script para eliminar un producto de la base de datos.
 * Utiliza consultas preparadas para evitar inyecciones SQL.
 * Guarda mensajes en sesión y redirige al panel principal.
 * 
 * @package Inventario
 * @version 1.0
 */

session_start();
include("db.php");

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Consulta segura usando prepared statement
    $stmt = $conn->prepare("DELETE FROM productos WHERE idProducto = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = '✅ Producto eliminado correctamente';
            $_SESSION['message_type'] = 'danger';
        } else {
            $_SESSION['message'] = '⚠️ Error al eliminar el producto.';
            $_SESSION['message_type'] = 'warning';
            error_log("Error al eliminar producto (ID $id): " . $stmt->error);
        }

        $stmt->close();
    } else {
        error_log("Error preparando consulta de eliminación: " . $conn->error);
    }

    $conn->close();
    header("Location: dashboard.php");
    exit;
} else {
    $_SESSION['message'] = '❌ ID de producto no válido.';
    $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
    exit;
}
?>
```

---

## Archivo: `delete_proveedor.php`

```php
<?php
/**
 * delete_proveedor.php
 *
 * Script para eliminar un proveedor de la base de datos.
 * Usa consultas preparadas para evitar inyecciones SQL.
 * Muestra mensajes de éxito o error mediante variables de sesión.
 * 
 * @package Inventario
 * @version 1.0
 */

session_start(); // Inicia la sesión para manejar mensajes
include("db.php"); // Conexión a la base de datos

// Verifica si se recibió el parámetro 'id' por la URL y que sea numérico
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id']; // Convierte el ID a número entero

    // Prepara una consulta SQL segura para eliminar el proveedor
    $stmt = $conn->prepare("DELETE FROM proveedores WHERE idProveedor = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id); // Asocia el valor del parámetro
        if ($stmt->execute()) {
            // Si la eliminación fue exitosa
            $_SESSION['message'] = '✅ Proveedor eliminado correctamente';
            $_SESSION['message_type'] = 'danger';
        } else {
            // Si hubo un error al ejecutar la consulta
            $_SESSION['message'] = '⚠️ Error al eliminar el proveedor.';
            $_SESSION['message_type'] = 'warning';
            error_log("Error al eliminar proveedor (ID $id): " . $stmt->error);
        }
        $stmt->close();
    } else {
        // Si hubo un error al preparar la consulta
        error_log("Error preparando eliminación de proveedor: " . $conn->error);
    }

    $conn->close();
    header("Location: dashboard.php");
    exit; // Detiene la ejecución tras redirigir

} else {
    // Si el ID no es válido o no se recibió
    $_SESSION['message'] = '❌ ID de proveedor no válido.';
    $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
    exit;
}
?>
```

---

## Archivo: `delete_venta.php`

```php
<?php
/**
 * delete_venta.php
 *
 * Este script elimina una venta de la base de datos y restaura el stock 
 * de los productos involucrados en la misma. Utiliza transacciones 
 * para asegurar la integridad de los datos.
 *
 * @package Inventario
 * @version 1.1
 */

include("db.php"); // Conexión a la base de datos

// Verifica que el ID de la venta se haya enviado por la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idVenta = (int) $_GET['id'];

    // Asegura que la sesión esté iniciada antes de usar $_SESSION
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Inicia una transacción para mantener la consistencia de los datos
    mysqli_begin_transaction($conn);

    try {
        /**
         * 1️⃣ Obtener los productos y cantidades asociados a la venta
         *    Esto permitirá restaurar el stock correctamente.
         */
        $query_detalles = "SELECT productoId, cantidad FROM detalle_venta WHERE ventaId = ?";
        $stmt_detalles = mysqli_prepare($conn, $query_detalles);
        mysqli_stmt_bind_param($stmt_detalles, "i", $idVenta);
        mysqli_stmt_execute($stmt_detalles);
        $result_detalles = mysqli_stmt_get_result($stmt_detalles);
        $detalles_venta = mysqli_fetch_all($result_detalles, MYSQLI_ASSOC);

        /**
         * 2️⃣ Restaurar el stock de cada producto vendido
         *    Si no hay detalles, simplemente se elimina la venta.
         */
        if (!empty($detalles_venta)) {
            foreach ($detalles_venta as $detalle) {
                $productoId = (int) $detalle['productoId'];
                $cantidadDevolver = (int) $detalle['cantidad'];

                $query_update_stock = "UPDATE productos SET cantidad = cantidad + ? WHERE idProducto = ?";
                $stmt_update_stock = mysqli_prepare($conn, $query_update_stock);
                mysqli_stmt_bind_param($stmt_update_stock, "ii", $cantidadDevolver, $productoId);

                if (!mysqli_stmt_execute($stmt_update_stock)) {
                    throw new Exception("Error al devolver el stock del producto ID: $productoId");
                }
                mysqli_stmt_close($stmt_update_stock);
            }
        }

        /**
         * 3️⃣ Eliminar la venta principal
         *    Si la relación tiene ON DELETE CASCADE, los detalles se eliminan automáticamente.
         */
        $query_delete_venta = "DELETE FROM ventas WHERE idVenta = ?";
        $stmt_delete_venta = mysqli_prepare($conn, $query_delete_venta);
        mysqli_stmt_bind_param($stmt_delete_venta, "i", $idVenta);

        if (!mysqli_stmt_execute($stmt_delete_venta)) {
            throw new Exception("Error al eliminar la venta principal (ID: $idVenta).");
        }

        // Confirmar transacción si todo fue correcto
        mysqli_commit($conn);

        $_SESSION['message'] = '✅ Venta eliminada correctamente y stock restaurado.';
        $_SESSION['message_type'] = 'success';

    } catch (Exception $e) {
        // Si ocurre un error, revertir todos los cambios
        mysqli_rollback($conn);
        $_SESSION['message'] = '❌ Error al eliminar la venta: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';

        // Registrar el error en el log del servidor
        error_log("Error eliminando venta ID $idVenta: " . $e->getMessage());
    }

    // Redirigir de vuelta al listado de ventas
    header("Location: listado_ventas.php");
    exit();

} else {
    // Si el ID no es válido o no se envió
    die("⚠️ ID de venta no proporcionado o inválido.");
}
?>
```

---

## Archivo: `edit_entrada.php`

```php
<?php
/**
 * edit_entrada.php
 *
 * Archivo encargado de permitir la edición de una entrada de producto en el inventario.
 * Permite modificar la fecha, producto, cantidad, precio de compra y proveedor asociado.
 * 
 * El proceso incluye:
 *  - Cargar los datos actuales de la entrada seleccionada.
 *  - Actualizar la cantidad del producto anterior y del nuevo producto.
 *  - Guardar los nuevos datos en la base de datos.
 *  - Redirigir al panel principal (dashboard.php) con un mensaje de confirmación.
 *
 * @author  
 * @version 1.0
 * @since   2025
 */

include("db.php"); // Conexión a la base de datos

// ==========================
// OBTENER DATOS DE LA ENTRADA A EDITAR
// ==========================
if (isset($_GET['id'])) {
    $idEntrada = $_GET['id'];
    // Consulta para obtener los datos de la entrada
    $query = "SELECT * FROM EntradaProductos WHERE idEntrada = $idEntrada";
    $result = mysqli_query($conn, $query);

    // Si existe la entrada, se obtienen sus valores
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);
        $fechaEntrada = $row['fechaEntrada'];
        $productoId = $row['productoId'];
        $cantidadComprada = $row['cantidadComprada'];
        $precioCompraUnidad = $row['precioCompraUnidad'];
        $proveedorId = $row['proveedorId'];
    }
}

// ==========================
// PROCESAR ACTUALIZACIÓN DE DATOS
// ==========================
if (isset($_POST['update'])) {
    $idEntrada = $_GET['id'];
    $fechaEntrada = $_POST['fechaEntrada'];
    $nuevoProductoId = $_POST['productoId'];
    $nuevaCantidadComprada = $_POST['cantidadComprada'];
    $precioCompraUnidad = $_POST['precioCompraUnidad'];
    $nuevoProveedorId = $_POST['proveedorId'];

    // Obtener la cantidad y el producto anteriores de la entrada
    $query = "SELECT cantidadComprada, productoId FROM EntradaProductos WHERE idEntrada = $idEntrada";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    $cantidadAnterior = $row['cantidadComprada'];
    $productoIdAnterior = $row['productoId'];

    // Restar la cantidad anterior del producto anterior
    $query = "UPDATE Productos SET cantidad = cantidad - $cantidadAnterior WHERE idProducto = $productoIdAnterior";
    mysqli_query($conn, $query);

    // Sumar la nueva cantidad al nuevo producto
    $query = "UPDATE Productos SET cantidad = cantidad + $nuevaCantidadComprada WHERE idProducto = $nuevoProductoId";
    mysqli_query($conn, $query);

    // Actualizar los datos de la entrada en la base de datos
    $query = "UPDATE EntradaProductos 
              SET fechaEntrada = '$fechaEntrada', 
                  productoId = '$nuevoProductoId', 
                  cantidadComprada = '$nuevaCantidadComprada', 
                  precioCompraUnidad = '$precioCompraUnidad', 
                  proveedorId = '$nuevoProveedorId' 
              WHERE idEntrada = $idEntrada";
    mysqli_query($conn, $query);

    // Mensaje de éxito y redirección
    $_SESSION['message'] = 'Entrada actualizada correctamente';
    $_SESSION['message_type'] = 'success';
    header("Location: dashboard.php");
}
?>

<?php include("includes/header.php") ?>

<!-- ==========================
     FORMULARIO DE EDICIÓN
     ========================== -->
<div class="container mt-5">
    <h2 class="mb-4">Editar Entrada</h2>
    <form action="edit_entrada.php?id=<?php echo $_GET['id']; ?>" method="POST">
        <!-- Fecha de entrada -->
        <div class="form-group">
            <label for="fechaEntrada">Fecha de Entrada</label>
            <input type="date" class="form-control" id="fechaEntrada" name="fechaEntrada" value="<?php echo $fechaEntrada; ?>" required>
        </div>

        <!-- Producto -->
        <div class="form-group">
            <label for="productoId">Producto</label>
            <select class="form-control" id="productoId" name="productoId" required>
                <?php
                // Cargar productos disponibles en la base de datos
                $query = "SELECT idProducto, nombreProducto FROM Productos";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $selected = ($row['idProducto'] == $productoId) ? 'selected' : '';
                    echo "<option value='{$row['idProducto']}' $selected>{$row['nombreProducto']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Cantidad comprada -->
        <div class="form-group">
            <label for="cantidadComprada">Cantidad Comprada</label>
            <input type="number" class="form-control" id="cantidadComprada" name="cantidadComprada" value="<?php echo $cantidadComprada; ?>" required>
        </div>

        <!-- Precio por unidad -->
        <div class="form-group">
            <label for="precioCompraUnidad">Precio de Compra por Unidad</label>
            <input type="number" step="0.01" class="form-control" id="precioCompraUnidad" name="precioCompraUnidad" value="<?php echo $precioCompraUnidad; ?>" required>
        </div>

        <!-- Proveedor -->
        <div class="form-group">
            <label for="proveedorId">Proveedor</label>
            <select class="form-control" id="proveedorId" name="proveedorId" required>
                <?php
                // Cargar proveedores disponibles
                $query = "SELECT idProveedor, nombreProveedor FROM Proveedores";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $selected = ($row['idProveedor'] == $proveedorId) ? 'selected' : '';
                    echo "<option value='{$row['idProveedor']}' $selected>{$row['nombreProveedor']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Botón de actualización -->
        <button type="submit" class="btn btn-primary mx-4 my-4" name="update">Actualizar</button>
    </form>
</div>

<?php include("includes/footer.php") ?>
```

---

## Archivo: `edit_producto.php`

```php
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
```

---

## Archivo: `edit_proveedor.php`

```php
<?php
/**
 * edit_proveedor.php
 *
 * Página para editar la información de un proveedor.
 *
 * Cambios realizados:
 * - Se añadieron comentarios y PHPDoc en español.
 * - Se encapsularon operaciones de lectura/actualización en funciones usando
 *   sentencias preparadas para mejorar la seguridad frente a inyección SQL.
 *
 * NOTA: Este archivo sigue en estilo procedural para mantener compatibilidad
 * con el proyecto; las funciones añadidas son auxiliares y devuelven datos
 * simples para la plantilla HTML al final del archivo.
 */

include("db.php");

/**
 * Obtiene un proveedor por su ID.
 *
 * @param mysqli $conn Conexión mysqli activa.
 * @param int $id Identificador del proveedor.
 * @return array|false Devuelve un array asociativo con los datos del proveedor o false si no existe o hay error.
 */
function obtenerProveedorPorId($conn, int $id)
{
    $stmt = $conn->prepare("SELECT * FROM Proveedores WHERE idProveedor = ?");
    if (!$stmt) return false;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return false;
}

/**
 * Actualiza los datos de un proveedor.
 *
 * @param mysqli $conn Conexión mysqli activa.
 * @param int $id Identificador del proveedor a actualizar.
 * @param array $data Array asociativo con las claves: nombreProveedor, descripcionProveedor,
 *                    direccionProveedor, telefono, Correo, infoAdicional.
 * @return bool True si la actualización se realizó correctamente, false en caso contrario.
 */
function actualizarProveedor($conn, int $id, array $data): bool
{
    $stmt = $conn->prepare("UPDATE Proveedores SET nombreProveedor = ?, descripcionProveedor = ?, direccionProveedor = ?, telefono = ?, Correo = ?, infoAdicional = ? WHERE idProveedor = ?");
    if (!$stmt) return false;
    $stmt->bind_param('ssssssi', $data['nombreProveedor'], $data['descripcionProveedor'], $data['direccionProveedor'], $data['telefono'], $data['Correo'], $data['infoAdicional'], $id);
    return $stmt->execute();
}

$registroActualizado = false;

// --- Manejo de la petición GET (cargar datos del proveedor) ---
if (isset($_GET['id'])) {
    // Aseguramos que $id sea un entero
    $id = (int) $_GET['id'];
    $row = obtenerProveedorPorId($conn, $id);
    if ($row === false) {
        echo "Proveedor no encontrado.";
        exit();
    }
}

// --- Manejo de la petición POST (actualizar proveedor) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recogemos y saneamos las entradas del formulario
    $nombreProveedor = isset($_POST['nombreProveedor']) ? trim($_POST['nombreProveedor']) : '';
    $descripcionProveedor = isset($_POST['descripcionProveedor']) ? trim($_POST['descripcionProveedor']) : '';
    $direccionProveedor = isset($_POST['direccionProveedor']) ? trim($_POST['direccionProveedor']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $correo = isset($_POST['Correo']) ? trim($_POST['Correo']) : '';
    $infoAdicional = isset($_POST['infoAdicional']) ? trim($_POST['infoAdicional']) : '';

    $data = [
        'nombreProveedor' => $nombreProveedor,
        'descripcionProveedor' => $descripcionProveedor,
        'direccionProveedor' => $direccionProveedor,
        'telefono' => $telefono,
        'Correo' => $correo,
        'infoAdicional' => $infoAdicional
    ];

    // Intentamos actualizar usando la función segura
    if (actualizarProveedor($conn, $id, $data)) {
        // Mensaje de sesión (si existe session_start() en includes/header.php lo usará)
        $_SESSION['message'] = 'Entrada actualizada correctamente';
        $_SESSION['message_type'] = 'success';
        
        header("Location: dashboard.php");
        exit();
    } else {
        // Mostramos el error bruto de MySQL para depuración (se puede mejorar)
        echo "Error actualizando registro: " . $conn->error;
    }
    $conn->close();
}
?>

<?php include("includes/header.php") ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Proveedor</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Editar Proveedor</h2>
    <form action="edit_proveedor.php?id=<?php echo $id; ?>" method="POST">
        <div class="form-group">
            <label for="nombreProveedor">Nombre</label>
            <input type="text" class="form-control" id="nombreProveedor" name="nombreProveedor" value="<?php echo $row['nombreProveedor']; ?>" required>
        </div>
        <div class="form-group">
            <label for="descripcionProveedor">Descripción</label>
            <input type="text" class="form-control" id="descripcionProveedor" name="descripcionProveedor" value="<?php echo $row['descripcionProveedor']; ?>" required>
        </div>
        <div class="form-group">
            <label for="direccionProveedor">Dirección</label>
            <input type="text" class="form-control" id="direccionProveedor" name="direccionProveedor" value="<?php echo $row['direccionProveedor']; ?>" required>
            </div>
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo $row['telefono']; ?>" required>
        </div>
        <div class="form-group">
            <label for="Correo">Correo</label>
            <input type="email" class="form-control" id="Correo" name="Correo" value="<?php echo $row['Correo']; ?>" required>
        </div>
        <div class="form-group">
            <label for="infoAdicional">Información Adicional</label>
            <input type="text" class="form-control" id="infoAdicional" name="infoAdicional" value="<?php echo $row['infoAdicional']; ?>">
        </div>
        <button type="submit" class="btn btn-primary mx-4 my-4">Actualizar</button>
    </form>
</div>
```

---

## Archivo: `edit_venta.php`

```php
<?php
/**
 * edit_venta.php
 *
 * Página para editar una venta existente en la base de datos.
 * 
 * Funcionalidades principales:
 * - Obtiene los datos actuales de una venta mediante su ID.
 * - Permite editar la información de la venta (producto, cantidad, vendedor, etc.).
 * - Recalcula el total de la venta según el precio del producto y la nueva cantidad.
 * - Ajusta automáticamente el inventario de productos (resta y suma las cantidades).
 * - Actualiza los datos en la base de datos mediante sentencias preparadas.
 * 
 * Nota: No se modificó la lógica del código original, solo se añadieron comentarios descriptivos.
 */

include("db.php");

// --- BLOQUE 1: Obtener información de la venta seleccionada ---
if (isset($_GET['id'])) {
    $idVenta = $_GET['id'];

    // Consulta para obtener los datos actuales de la venta
    $query = "SELECT * FROM ventas WHERE idVenta = $idVenta";
    $result = mysqli_query($conn, $query);

    // Si la venta existe, se almacenan sus datos para mostrarlos en el formulario
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);
        $fechaVenta = $row['fechaVenta'];
        $productoId = $row['productoId'];
        $cantidadVenta = $row['cantidadVenta'];
        $precioVentaTotal = $row['precioVentaTotal'];
        $vendedorId = $row['vendedorId'];
    }
}

// --- BLOQUE 2: Actualizar la venta (al enviar el formulario) ---
if (isset($_POST['update'])) {
    $idVenta = $_GET['id'];
    $fechaVenta = $_POST['fechaVenta'];
    $nuevoProductoId = $_POST['productoId'];
    $nuevaCantidadVenta = $_POST['cantidadVenta'];

    // Obtener el precio unitario del producto seleccionado
    $query = "SELECT precioVenta FROM productos WHERE idProducto = $productoId";
    $result_precio = mysqli_query($conn, $query);

    if (!$result_precio) {
        die("Query Failed: " . mysqli_error($conn));
    }

    // Calcular el nuevo precio total según la cantidad
    $row_precio = mysqli_fetch_assoc($result_precio);
    $precioVenta = $row_precio['precioVenta'];
    $nuevoPrecioVentaTotal = $precioVenta * $nuevaCantidadVenta;

    $nuevoVendedorId = $_POST['vendedorId'];

    // --- Ajuste de inventario ---
    // 1. Recuperar la cantidad anterior y el producto anterior
    $query = "SELECT cantidadVenta, productoId FROM ventas WHERE idVenta = $idVenta";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    $cantidadAnterior = $row['cantidadVenta'];
    $productoIdAnterior = $row['productoId'];

    // 2. Reponer la cantidad anterior al inventario del producto previo
    $query = "UPDATE Productos SET cantidad = cantidad + $cantidadAnterior WHERE idProducto = $productoIdAnterior";
    mysqli_query($conn, $query);

    // 3. Descontar la nueva cantidad al producto seleccionado
    $query = "UPDATE Productos SET cantidad = cantidad - $nuevaCantidadVenta WHERE idProducto = $nuevoProductoId";
    mysqli_query($conn, $query);

    // --- Actualizar los datos de la venta ---
    $sql = "UPDATE ventas 
            SET fechaVenta = ?, productoId = ?, cantidadVenta = ?, precioVentaTotal = ?, vendedorId = ? 
            WHERE idVenta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siidii", $fechaVenta, $nuevoProductoId, $nuevaCantidadVenta, $nuevoPrecioVentaTotal, $nuevoVendedorId, $idVenta);

    if ($stmt->execute()) {
        // Mensaje de confirmación
        $_SESSION['message'] = 'Venta actualizada correctamente';
        $_SESSION['message_type'] = 'success';
    } else {
        echo "Error:<hr> " . $sql . "<hr>" . $conn->error;
    }

    // Redirigir al panel principal
    header("Location: dashboard.php");
}
?>

<?php include("includes/header.php") ?>

<!-- BLOQUE 3: Formulario de edición -->
<div class="container mt-5">
    <h2 class="mb-4">Editar Venta</h2>
    <form action="edit_venta.php?id=<?php echo $_GET['id']; ?>" method="POST">

        <!-- Fecha de venta -->
        <div class="form-group">
            <label for="fechaVenta">Fecha de Venta</label>
            <input type="date" class="form-control" id="fechaVenta" name="fechaVenta" value="<?php echo $fechaVenta; ?>" required>
        </div>

        <!-- Producto -->
        <div class="form-group">
            <label for="productoId">Producto</label>
            <select class="form-control" id="productoId" name="productoId" required>
                <?php
                $query = "SELECT idProducto, nombreProducto FROM Productos";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $selected = ($row['idProducto'] == $productoId) ? 'selected' : '';
                    echo "<option value='{$row['idProducto']}' $selected>{$row['nombreProducto']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Cantidad -->
        <div class="form-group">
            <label for="cantidadVenta">Cantidad Vendida</label>
            <input type="number" class="form-control" id="cantidadVenta" name="cantidadVenta" value="<?php echo $cantidadVenta; ?>" required>
        </div>
        
        <!-- Vendedor -->
        <div class="form-group">
            <label for="vendedorId">Vendedor</label>
            <select class="form-control" id="vendedorId" name="vendedorId" required>
                <?php
                $query = "SELECT idUsuario, nombreCompleto FROM usuario";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $selected = ($row['idUsuario'] == $vendedorId) ? 'selected' : '';
                    echo "<option value='{$row['idUsuario']}' $selected>{$row['nombreCompleto']}</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mx-4 my-4" name="update">Actualizar</button>
    </form>
</div>

<?php include("includes/footer.php") ?>
```

---

## Archivo: `entradaproductos.php`

```php
<?php
/**
 * entradaproductos.php
 *
 * Página para gestionar y registrar las entradas de productos al inventario.
 * 
 * Funcionalidad:
 * - Carga la lista de productos y proveedores desde la base de datos.
 * - Muestra un formulario para registrar una nueva entrada de producto.
 * - Envía los datos a `save_entrada.php` para ser guardados.
 * 
 * Notas:
 * - Se usan sentencias preparadas para mayor seguridad y eficiencia.
 * - Incluye validación en el lado del cliente mediante Bootstrap.
 */

// --- Conexión a la base de datos ---
require_once("db.php");

// --- BLOQUE 1: Obtener productos disponibles ---
// Consulta preparada para obtener los productos registrados en la base de datos.
$query_productos = "SELECT idProducto, nombreProducto FROM Productos";
$stmt_productos = mysqli_prepare($conn, $query_productos);
mysqli_stmt_execute($stmt_productos);
$result_productos = mysqli_stmt_get_result($stmt_productos);
$productos = mysqli_fetch_all($result_productos, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_productos);

// --- BLOQUE 2: Obtener proveedores registrados ---
// Consulta preparada para obtener los proveedores disponibles.
$query_proveedores = "SELECT idProveedor, nombreProveedor FROM Proveedores";
$stmt_proveedores = mysqli_prepare($conn, $query_proveedores);
mysqli_stmt_execute($stmt_proveedores);
$result_proveedores = mysqli_stmt_get_result($stmt_proveedores);
$proveedores = mysqli_fetch_all($result_proveedores, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_proveedores);

// --- BLOQUE 3: Cargar encabezado HTML ---
require_once("includes/header.php");
?>

<!-- BLOQUE 4: Formulario principal de registro de entrada -->
<div class="container mt-5">
    <h2 class="mb-4">Registrar Entrada de Productos</h2>

    <!-- 
        Formulario para registrar una entrada de producto.
        Se envía mediante método POST a "save_entrada.php".
        Incluye validación con Bootstrap (clase 'needs-validation').
    -->
    <form action="save_entrada.php" method="POST" class="needs-validation" novalidate>

        <!-- Campo: Fecha de entrada -->
        <div class="form-group">
            <label for="fechaEntrada">Fecha de Entrada:</label>
            <input type="date" class="form-control" id="fechaEntrada" name="fechaEntrada" value="" required>
            <div class="invalid-feedback">Por favor, ingrese la fecha de entrada.</div>
        </div>

        <!-- Campo: Producto -->
        <div class="form-group">
            <label for="productoId">Producto:</label>
            <select class="form-control" id="productoId" name="productoId" required>
                <option value="">Seleccione un producto</option>
                <?php foreach ($productos as $producto): ?>
                    <option value="<?php echo $producto['idProducto']; ?>">
                        <?php echo $producto['nombreProducto']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Por favor, seleccione un producto.</div>
        </div>

        <!-- Campo: Cantidad comprada -->
        <div class="form-group">
            <label for="cantidadComprada">Cantidad Comprada:</label>
            <input type="number" class="form-control" id="cantidadComprada" name="cantidadComprada" value="" required min="1">
            <div class="invalid-feedback">Por favor, ingrese la cantidad comprada.</div>
            <small class="form-text text-muted">Ingrese un número mayor que cero.</small>
        </div>

        <!-- Campo: Precio de compra por unidad -->
        <div class="form-group">
            <label for="precioCompraUnidad">Precio de Compra por Unidad:</label>
            <input type="number" step="0.01" class="form-control" id="precioCompraUnidad" name="precioCompraUnidad"
                   value="" required min="0.01">
            <div class="invalid-feedback">Por favor, ingrese el precio de compra por unidad.</div>
            <small class="form-text text-muted">Ingrese un número mayor que cero.</small>
        </div>

        <!-- Campo: Proveedor -->
        <div class="form-group">
            <label for="proveedorId">Proveedor:</label>
            <select class="form-control" id="proveedorId" name="proveedorId" required>
                <option value="">Seleccione un proveedor</option>
                <?php foreach ($proveedores as $proveedor): ?>
                    <option value="<?php echo $proveedor['idProveedor']; ?>">
                        <?php echo $proveedor['nombreProveedor']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Por favor, seleccione un proveedor.</div>
        </div>

        <!-- Botón para registrar -->
        <input type="submit" class="btn btn-success btn-block mx-4 my-4" name="save_entrada" value="Registrar Entrada">
    </form>
</div>

<?php
// --- BLOQUE 5: Incluir pie de página ---
require_once("includes/footer.php");
?>

<!-- BLOQUE 6: Validación del formulario con JavaScript -->
<script>
    // Script de validación de formularios (Bootstrap 4)
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
```

---

## Archivo: `exportar_informe.php`

```php
<?php
/**
 * exportar_informe.php
 *
 * Genera y exporta informes de ventas en formato PDF utilizando la librería FPDF.
 * Si la librería no está instalada, crea una clase base vacía (stub) para evitar errores de ejecución.
 * El script obtiene los datos desde la base de datos (usando queries/informe_queries.php)
 * y los presenta en una tabla dentro de un PDF.
 */

// Intentar cargar la librería FPDF si existe en la carpeta fpdf/
if (!class_exists('FPDF')) {
    $fpdfPath = __DIR__ . '/fpdf/fpdf.php';
    if (file_exists($fpdfPath)) {
        // Se incluye la librería si el archivo existe
        require_once $fpdfPath;
    } else {
        // En caso de no encontrar FPDF, se lanza una advertencia
        trigger_error('FPDF no encontrado en ' . $fpdfPath . ' — algunos exports PDF no funcionarán hasta instalar la librería.', E_USER_WARNING);
    }
}

// Si la clase FPDF aún no existe, se define un stub básico
if (!class_exists('FPDF')) {
    /**
     * Clase FPDF ficticia para evitar errores cuando la librería real no está disponible.
     * Solo contiene métodos vacíos necesarios para que el código no falle.
     */
    class FPDF {
        public function __construct() {}
        public function AliasNbPages() {}
        public function AddPage($orientation='P', $size='A4') {}
        public function SetFont($family='', $style='', $size=0) {}
        public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false) {}
        public function Ln($h=0) {}
        public function SetY($y) {}
        public function PageNo() { return 1; }
        public function SetFillColor($r,$g,$b) {}
        public function SetTextColor($v) {}
        public function SetDrawColor($r,$g,$b) {}
        public function SetLineWidth($width) {}
        public function Output($dest='I', $name='doc.pdf') {}
    }
}

// Conexión a la base de datos y carga de las consultas del informe
require('db.php');
require('queries/informe_queries.php');

/**
 * Clase PDF personalizada que extiende FPDF
 * Define la estructura del encabezado, pie de página y tabla de datos del informe.
 */
class PDF extends FPDF
{
    /**
     * Cabecera del documento PDF.
     * Muestra el título centrado en la parte superior de la página.
     */
    function Header()
    {
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Informe de Ventas',0,1,'C');
        $this->Ln(10);
    }

    /**
     * Pie de página del documento PDF.
     * Muestra la numeración de páginas en la parte inferior.
     */
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
    }

    /**
     * Genera una tabla con los datos del informe.
     *
     * @param array $header Encabezados de la tabla.
     * @param array $data Datos a mostrar (cada fila representa una venta).
     */
    function CreateTable($header, $data)
    {
        // Configuración inicial de colores, líneas y fuente
        $this->SetFillColor(2, 11, 105);  // Color de fondo del encabezado
        $this->SetTextColor(255);         // Texto blanco
        $this->SetDrawColor(128,0,0);     // Color de los bordes
        $this->SetLineWidth(.3);
        $this->SetFont('','B');

        // Definición de los anchos de las columnas
        $w = array(40, 95, 55);

        // Dibujar los encabezados
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',true);
        $this->Ln();

        // Restaurar colores y fuentes para las filas de datos
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');

        // Variable para alternar colores de fila
        $fill = false;
        $totalGeneral = 0;

        // Iterar sobre cada fila de datos del informe
        foreach($data as $row)
        {
            $this->Cell($w[0],6,$row['fechaVenta'],'LR',0,'L',$fill);
            $this->Cell($w[1],6,utf8_decode($row['nombrecompleto']),'LR',0,'L',$fill);
            $this->Cell($w[2],6,'$'.number_format($row['total'], 2),'LR',0,'R',$fill);
            $this->Ln();
            $fill = !$fill;
            $totalGeneral += $row['total'];
        }

        // Línea final de cierre de la tabla
        $this->Cell(array_sum($w),0,'','T');
        $this->Ln();

        // Mostrar total general al final de la tabla
        $this->SetFont('','B');
        $this->Cell($w[0] + $w[1], 7, 'Total General:', 'T', 0, 'R');
        $this->Cell($w[2], 7, '$'.number_format($totalGeneral, 2), 'T', 0, 'R');
    }
}

// Verificar que se hayan recibido los parámetros de fecha
if (isset($_GET['start']) && isset($_GET['end'])) {
    $start = $_GET['start'];
    $end = $_GET['end'];
    $vendedorId = isset($_GET['usuario']) && $_GET['usuario'] !== '' ? $_GET['usuario'] : null;
    
    // Obtener datos del informe desde la base de datos
    $reportData = getSalesReportByDateRange($conn, $start, $end, $vendedorId);

    // Crear el PDF e iniciar configuración básica
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('P', 'A4');
    $pdf->SetFont('Arial','',12);
    
    // Encabezados de las columnas del informe
    $header = array('Fecha', 'Vendedor', 'Total Vendido');
    
    // Verificar si existen datos
    if(!empty($reportData)) {
        $pdf->CreateTable($header, $reportData);
    } else {
        // Mostrar mensaje si no hay resultados
        $pdf->Cell(0,10,'No se encontraron resultados para el rango de fechas seleccionado.',0,1);
    }

    // Descargar el informe generado
    $pdf->Output('D', 'Informe_Ventas.pdf');

} else {
    // Si no se reciben parámetros, mostrar advertencia
    echo "Por favor, especifique un rango de fechas.";
}
?>
```

---

## Archivo: `generar_factura.php`

```php
<?php
/**
 * generar_factura.php
 *
 * Genera un documento PDF con los detalles de una venta (factura) usando la librería FPDF.
 * Incluye información del vendedor, productos vendidos y el total de la venta.
 * 
 * Flujo general:
 * 1. Se valida que el ID de la venta sea correcto.
 * 2. Se obtienen los datos de la venta desde la base de datos.
 * 3. Se construye un PDF con formato de factura utilizando la clase personalizada PDF_Invoice.
 */

require('fpdf.php');           // Librería FPDF para generar archivos PDF
require('db.php');                  // Conexión a la base de datos
require('queries/venta_querie.php');// Funciones para obtener los datos de venta

// Validar que se haya recibido un ID de venta válido por GET
if (!isset($_GET['id_venta']) || !is_numeric($_GET['id_venta'])) {
    die("ID de venta no válido.");
}

$idVenta = (int)$_GET['id_venta']; // Conversión segura a entero

// Obtener los datos de la venta (incluye información general e ítems vendidos)
$data = getVentaDetailsById($conn, $idVenta);

if (!$data) {
    die("No se encontraron datos para la venta con ID: " . $idVenta);
}

/**
 * Clase PDF_Invoice
 * Extiende la clase FPDF para crear una factura personalizada con encabezado, pie y tabla de productos.
 */
class PDF_Invoice extends FPDF
{
    /**
     * Cabecera de la factura.
     * Incluye el título principal (FACTURA) y opcionalmente un logo.
     */
    function Header()
    {
        // Logo (comentado, se puede habilitar si existe la imagen)
        // $this->Image('imagenes/logo1.png',10,6,30);
        $this->SetFont('Arial','B',18);
        $this->Cell(80);
        $this->Cell(30,10,utf8_decode('FACTURA'),0,0,'C');
        $this->Ln(20);
    }

    /**
     * Pie de página de la factura.
     * Muestra un mensaje de agradecimiento y el número de página.
     */
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode('Gracias por su compra'),0,0,'C');
        $this->SetX(-35);
        $this->Cell(0,10,utf8_decode('Página ').$this->PageNo(),0,0,'C');
    }

    /**
     * Muestra los datos principales de la factura (empresa, fecha, vendedor, etc.).
     *
     * @param array $venta Datos generales de la venta.
     */
    function InvoiceDetails($venta)
    {
        // Información de la empresa emisora
        $this->SetFont('Arial','B',12);
        $this->Cell(0, 7, utf8_decode('Mi Empresa S.A.S'), 0, 1);
        $this->SetFont('','',10);
        $this->Cell(0, 6, utf8_decode('Dirección: Calle Falsa 123'), 0, 1);
        $this->Cell(0, 6, utf8_decode('Teléfono: 123-4567'), 0, 1);
        $this->Ln(10);

        // Información de la factura
        $this->SetFont('Arial','B',11);
        $this->Cell(40, 7, 'Factura Nro:', 0, 0);
        $this->SetFont('','',11);
        $this->Cell(100, 7, $venta['idVenta'], 0, 1);

        $this->SetFont('Arial','B',11);
        $this->Cell(40, 7, 'Fecha:', 0, 0);
        $this->SetFont('','',11);
        $this->Cell(100, 7, date("d/m/Y", strtotime($venta['fechaVenta'])), 0, 1);

        $this->SetFont('Arial','B',11);
        $this->Cell(40, 7, 'Vendedor:', 0, 0);
        $this->SetFont('','',11);
        $this->Cell(100, 7, utf8_decode($venta['vendedor']), 0, 1);
        $this->Ln(10);
    }

    /**
     * Genera la tabla con los productos de la venta.
     *
     * @param array $header Encabezados de las columnas (Producto, Cantidad, etc.)
     * @param array $items Lista de productos vendidos.
     */
    function ItemsTable($header, $items)
    {
        // Configurar encabezado de la tabla
        $this->SetFillColor(230,230,230);
        $this->SetFont('Arial','B',10);
        $w = array(90, 30, 35, 35); // Anchos de columnas

        // Dibujar encabezados
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',true);
        $this->Ln();

        // Dibujar filas de productos
        $this->SetFont('Arial','',10);
        $fill = false;
        foreach($items as $item)
        {
            $this->Cell($w[0],6,utf8_decode($item['nombreProducto']),'LR',0,'L',$fill);
            $this->Cell($w[1],6,$item['cantidadVendida'],'LR',0,'C',$fill);
            $this->Cell($w[2],6,'$'.number_format($item['precioUnitario'], 2),'LR',0,'R',$fill);
            $this->Cell($w[3],6,'$'.number_format($item['importe'], 2),'LR',0,'R',$fill);
            $this->Ln();
            $fill = !$fill; // Alterna el color de las filas
        }

        // Línea final inferior de la tabla
        $this->Cell(array_sum($w),0,'','T');
    }

    /**
     * Muestra el total general de la venta al final del documento.
     *
     * @param array $venta Datos generales de la venta (incluye totalVenta).
     */
    function Totals($venta)
    {
        $this->Ln(5);
        $this->SetFont('Arial','B',11);
        $this->Cell(120);
        $this->Cell(35, 7, 'TOTAL', 1, 0, 'C');
        $this->SetFont('','',11);
        $this->Cell(35, 7, '$'.number_format($venta['totalVenta'], 2), 1, 1, 'R');
    }
}

// Crear el documento PDF
$pdf = new PDF_Invoice();
$pdf->AliasNbPages();
$pdf->AddPage();

// Agregar los detalles de la factura
$pdf->InvoiceDetails($data['venta']);

// Crear tabla de ítems vendidos
$header = array('Producto', 'Cantidad', 'Precio Unit.', 'Importe');
$pdf->ItemsTable($header, $data['items']);

// Mostrar totales
$pdf->Totals($data['venta']);

// Descargar el archivo PDF con el nombre "Factura_ID.pdf"
$pdf->Output('D', 'Factura_'.$idVenta.'.pdf');
?>
```

---

## Archivo: `hash.php`

```php
<?php
/**
 * migrar_contrasenias.php
 *
 * Este script actualiza las contraseñas de los usuarios en la base de datos,
 * aplicando el algoritmo de hash bcrypt para mejorar la seguridad.
 * 
 * - Busca usuarios cuyas contraseñas aún no estén hasheadas o usen un algoritmo obsoleto.
 * - Si encuentra contraseñas con prefijo 'sha256:', las migra a bcrypt.
 * - Registra en la base de datos el algoritmo utilizado en cada actualización.
 */

// Incluye el archivo con la conexión a la base de datos.
require_once('db.php');

// Consulta que selecciona los usuarios cuyas contraseñas necesitan ser actualizadas.
// Incluye casos en los que no hay algoritmo definido o usan formatos antiguos.
$query = "SELECT idUsuario, contrasenia FROM Usuario 
          WHERE algoritmo_hash IS NULL 
          OR algoritmo_hash = 'bcrypt' 
          OR contrasenia LIKE 'sha256:%'";

$result = mysqli_query($conn, $query);

if ($result) {
    // Recorre cada usuario encontrado en la consulta.
    while ($row = mysqli_fetch_assoc($result)) {
        $idUsuario = $row['idUsuario'];
        $contraseniaPlano = $row['contrasenia'];
        $algoritmoHash = 'bcrypt'; // Se define bcrypt como el algoritmo por defecto.

        // Verifica si la contraseña ya estaba hasheada con SHA256.
        // Si es así, elimina el prefijo 'sha256:' y actualiza el tipo de algoritmo.
        if (strpos($contraseniaPlano, 'sha256:') === 0) {
            $contraseniaPlano = str_replace('sha256:', '', $contraseniaPlano);
            $algoritmoHash = 'SHA256'; // Conserva el registro del algoritmo original.
        }

        // Genera un nuevo hash usando bcrypt para la contraseña.
        $contraseniaHash = password_hash($contraseniaPlano, PASSWORD_BCRYPT);

        // Prepara la consulta para actualizar la contraseña y el algoritmo en la base de datos.
        $update_query = "UPDATE Usuario SET contrasenia = ?, algoritmo_hash = ? WHERE idUsuario = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ssi", $contraseniaHash, $algoritmoHash, $idUsuario);

        // Ejecuta la actualización y verifica si fue exitosa.
        $stmt->execute();

        if ($stmt->errno == 0) {
            echo "Contraseña del usuario $idUsuario actualizada correctamente a bcrypt.<br>";
        } else {
            echo "Error al actualizar la contraseña del usuario $idUsuario: " . $stmt->error . "<br>";
        }

        // Cierra la sentencia preparada.
        $stmt->close();
    }
} else {
    // En caso de error al realizar la consulta inicial.
    echo "Error al obtener los usuarios: " . mysqli_error($conn);
}

// Cierra la conexión a la base de datos.
mysqli_close($conn);
?>
```

---

## Archivo: `includes/footer.php`

```php
<?php
/**
 * includes/footer.php
 *
 * Pie de página común para las vistas. Encabezado en español añadido.
 */
?>

    <footer>
        <p>&copy; 2025 "InventarioInc". Todos los derechos reservados.

        <a target="_blank" href="https://github.com/Cristhian1025"><i class="fa-brands fa-github"></i></a>
        <a target="_blank" href="https://www.linkedin.com/in/cristhian-andrey-poveda-gaviria-21075a1b0/"><i class="fa-brands fa-linkedin"></i></a>
        </p>
    </footer>
    <!-- Scripts -->

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-...tu-código..." crossorigin="anonymous"></script>

</body>
</html>
```

---

## Archivo: `includes/header.php`

```php
<?php

$inactivity_timeout = 1800; //Segundos permitidos de inactividad - 30 minutos = 1800
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $inactivity_timeout)) {

    session_unset();     
    //session_destroy();   // Destruir sesión -> pero no imprime mensaje
    // Mensaje a imprimir
    $_SESSION['message'] = 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión de nuevo.';
    $_SESSION['message_type'] = 'Warning';
    session_write_close();
    header("Location: index.php"); // Redirigir a la página de inicio de sesión
    exit();
}

// Actualizar el timestamp de la última actividad a la hora actual
$_SESSION['LAST_ACTIVITY'] = time();


if (!isset($_SESSION['nombreUsuario'])) {  // Asegúrate de usar el mismo nombre de sesión
    header("Location: index.php"); // Redirige al inicio de sesión si no está autenticado
    exit();
}
?>
<?php
/**
 * includes/header.php
 *
 * Cabecera común para las páginas (barra de navegación, estilos, scripts).
 * Encabezado en español añadido.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Papelería</title>
    
    <!-- Uso de bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://kit.fontawesome.com/56435b6968.js" crossorigin="anonymous"></script>
</head>
<body>

<header class="navbar navbar-dark bg-dark">
    <div class="container">
        <img src="imagenes/logo1.png" alt="LOGO-DEFECTO" class="rounded" width="60" height="60">
        <a href="dashboard.php" class="navbar-brand"><h1>CONTROL INVENTARIO</h1></a>
        <div class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="imagenes/icon_user.png" alt="Usuario" class="rounded-circle" width="45" height="45">
                <span class="text-white"><?php echo $_SESSION['nombreUsuario']; ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="#" class="dropdown-item">Perfil</a>
                <a href="logout.php" class="dropdown-item">Cerrar sesión</a>
            </div>
        </div>
    </div>
</header>

<nav id="mainNav" class="navbar-expand-md navbar-dark bg-dark">
    <ul class="flex-column mt-3">
        <a href="dashboard.php" class="caja_nav icon-link icon-link-hover">Inicio</a>
        <a href="productos.php" class="caja_nav">Productos</a>
        <a href="proveedores.php" class="caja_nav">proveedores</a>
        <a href="entradaproductos.php" class="caja_nav">Entrada de Productos</a>
        <a href="ventas.php" class="caja_nav">Registrar Venta</a>
        <a href="listado_ventas.php" class="caja_nav">Listado de Ventas</a>
        <a href="informes.php" class="caja_nav">Informes</a>
    </ul>
</nav>

<script>
    window.addEventListener('scroll', function() {
        const mainNav = document.getElementById('mainNav');
        const fixedNavSpace = document.querySelector('.fixed-nav-space');
        const offset = 60; // Altura de la barra de navegación

        if (window.pageYOffset > offset) {
            mainNav.classList.add('fixed-top');
            if (!fixedNavSpace) {
                const spaceDiv = document.createElement('div');
                spaceDiv.classList.add('fixed-nav-space');
                mainNav.parentNode.insertBefore(spaceDiv, mainNav);
            }
        } else {
            mainNav.classList.remove('fixed-top');
            const fixedNavSpace = document.querySelector('.fixed-nav-space');
            if (fixedNavSpace) {
                fixedNavSpace.parentNode.removeChild(fixedNavSpace);
            }
        }
    });
</script>
```

---

## Archivo: `index.php`

```php
<?php
/**
 * index.php
 *
 * Página principal de acceso al sistema de Inventario Web.
 * 
 * Este archivo presenta el formulario de inicio de sesión para los usuarios.
 * Verifica si ya existe una sesión activa y, en caso afirmativo, redirige al panel principal.
 * También muestra mensajes de error o confirmación relacionados con el inicio de sesión.
 */

session_start(); // Inicia o reanuda la sesión actual del usuario.

// Verifica si el usuario ya ha iniciado sesión.
// Si existe una sesión activa, se redirige automáticamente al panel principal.
if (isset($_SESSION['nombreUsuario'])) {
    header("Location: dashboard.php");
    exit();
}

// Inicializa variables para mostrar mensajes (por ejemplo, errores o confirmaciones).
$message = null;
$message_type = 'danger'; // Valor por defecto en caso de no especificarse un tipo de mensaje.

// Si existen mensajes almacenados en la sesión, los recupera y luego los limpia.
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    if (isset($_SESSION['message_type'])) {
        $message_type = $_SESSION['message_type']; // Obtiene el tipo de mensaje (éxito, error, advertencia, etc.).
    }

    // Limpia los mensajes de la sesión para evitar repeticiones.
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar - Inventario Web</title>

    <!-- Carga de íconos Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <!-- Enlace a la hoja de estilos principal -->
    <link rel="stylesheet" href="css/style_1.css">
</head>

<body>
    <!-- Contenedor principal con descripción -->
    <div class="screen-1">
        <div class="caja1">
            <div class="name">
                <h1>INVENTARIO EN LA WEB</h1>
            </div>
            <p>Acceda y gestione su inventario <br> de su negocio desde el navegador.</p>
        </div>
    </div>

    <!-- Formulario de inicio de sesión -->
    <div class="screen-1">
        <form class="formulario" action="login.php" method="POST">
            
            <!-- Muestra mensajes de sesión (éxito o error) si existen -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Logo del sistema -->
            <div class="logo">
                <img title="loginIMG" class="logoindex" src="imagenes/inventario.jpg" alt="Logo del Sistema de Inventario" width="300" height="auto">
            </div>

            <!-- Campo de nombre de usuario -->
            <div class="email">
                <label for="usuario">Nombre de Usuario</label>
                <div class="sec-2">
                    <input type="text" id="usuario" name="usuario" placeholder="Username" required/>
                </div>
            </div>

            <!-- Campo de contraseña -->
            <div class="password">
                <label for="contrasenia">Contraseña</label>
                <div class="sec-2">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input class="pas" type="password" id="contrasenia" name="contrasenia" required placeholder="············"/>
                </div>
            </div>

            <!-- Botón de ingreso -->
            <button type="submit" class="login" name="login">Ingresar</button>

            <!-- Pie del formulario (puede usarse para enlaces u otra información) -->
            <div class="footer">
            </div>
        </form>
    </div>
</body>
</html>
```

---

## Archivo: `informes.php`

```php
<?php
/**
 * informes.php
 *
 * Página para la generación y visualización de informes de ventas.
 * Permite al usuario consultar el total de ventas por fecha y por vendedor,
 * así como exportar los resultados en formato PDF.
 *
 * @author  
 * @version 1.0
 * @date    2025-10-20
 */

include("db.php"); // Conexión a la base de datos.
require_once 'queries/informe_queries.php'; // Funciones SQL específicas para los informes.
include("includes/header.php"); // Encabezado HTML común del sitio (navegación, estilos, etc.).

// Inicializa variables
$reportData = []; // Almacena los datos del informe generados
$usuarios = getAllUsuarios($conn); // Obtiene la lista de vendedores registrados

// Verifica si el formulario fue enviado mediante método GET con los parámetros requeridos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['start']) && isset($_GET['end'])) {
    $start = $_GET['start']; // Fecha de inicio del rango
    $end = $_GET['end'];     // Fecha final del rango

    // Si se seleccionó un vendedor específico, se filtra por su ID
    $vendedorId = isset($_GET['usuario']) && $_GET['usuario'] !== '' ? $_GET['usuario'] : null;

    // Consulta los datos del informe usando el rango de fechas y, si aplica, el vendedor
    $reportData = getSalesReportByDateRange($conn, $start, $end, $vendedorId);
}
?>

<!-- Contenedor principal de la página de informes -->
<div class="container mt-4 mb-5 pb-5">
    <h2>Informe de Ventas</h2>

    <!-- Formulario de filtrado de informes -->
    <form method="GET" class="row g-3">
        <!-- Campo de fecha de inicio -->
        <div class="col-md-3">
            <label class="form-label">Desde:</label>
            <input type="date" name="start" class="form-control" required>
        </div>

        <!-- Campo de fecha de finalización -->
        <div class="col-md-3">
            <label class="form-label">Hasta:</label>
            <input type="date" name="end" class="form-control" required>
        </div>

        <!-- Selección opcional de vendedor -->
        <div class="col-md-3">
            <label class="form-label">Vendedor (opcional):</label>
            <select name="usuario" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['IdUsuario'] ?>">
                        <?= htmlspecialchars($usuario['nombrecompleto']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Botón para generar informe -->
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Generar</button>
        </div>

        <!-- Botón para exportar informe a PDF (abre en nueva pestaña) -->
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" formtarget="_blank" formaction="exportar_informe.php" class="btn btn-success w-100">
                Exportar PDF
            </button>
        </div>
    </form>

    <!-- Si existen datos, se muestran en una tabla -->
    <?php if ($reportData): ?>
        <table class="table table-striped table-bordered mt-4 mb-5">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Vendedor</th>
                    <th>Nro. Ventas</th>
                    <th>Total Vendido</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalGeneral = 0; // Acumulador para el total general de ventas

                // Recorre los resultados del informe
                foreach ($reportData as $row): 
                    $totalGeneral += $row['total']; // Suma el total de cada día o vendedor
                ?>
                    <tr>
                        <td><?= $row['fechaVenta'] ?></td>
                        <td><?= htmlspecialchars($row['nombrecompleto']) ?></td>
                        <td><?= htmlspecialchars($row['numero_ventas']) ?></td>
                        <td>$<?= number_format($row['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Fila con el total general -->
                <tr class="table-secondary fw-bold">
                    <td colspan="3" class="text-end">Total General:</td>
                    <td>$<?= number_format($totalGeneral, 2) ?></td>
                </tr>
            </tbody>
        </table>

    <!-- Si se hizo una búsqueda pero no se encontraron resultados -->
    <?php elseif ($_GET): ?>
        <p class="mt-4 alert alert-warning">No se encontraron resultados.</p>
    <?php endif; ?>
</div>

<!-- Pie de página del sitio -->
<?php include("includes/footer.php"); ?>
```

---

## Archivo: `listado_ventas.php`

```php
<?php
/**
 * listado_ventas.php
 *
 * Descripción:
 * Página encargada de listar todas las ventas registradas en el sistema, con paginación y opciones
 * para ver la factura o eliminar una venta. Se utiliza información obtenida desde la base de datos
 * mediante consultas preparadas definidas en `queries/venta_querie.php`.
 *
 * Funcionalidades principales:
 * - Mostrar listado de ventas con paginación.
 * - Permitir generar facturas en PDF para cada venta.
 * - Permitir eliminar una venta (con confirmación previa).
 *
 * Archivos relacionados:
 * - db.php → conexión a la base de datos.
 * - includes/header.php / includes/footer.php → estructura común de la interfaz.
 * - generar_factura.php → genera la factura en PDF.
 * - delete_venta.php → elimina la venta y actualiza el inventario.
 */

include("db.php"); // Conexión a la base de datos.
require_once 'queries/venta_querie.php'; // Funciones de consulta relacionadas con ventas.
include("includes/header.php"); // Encabezado de la página (barra de navegación, estilos, etc.).

/* -----------------------------------------------------------
   LÓGICA DE PAGINACIÓN
------------------------------------------------------------*/

// Número de registros a mostrar por página.
$recordsPerPage = 10;

// Página actual obtenida desde la URL (por defecto, la página 1).
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

// Obtener las ventas usando la función definida en venta_querie.php
$queryResult = getAllVentas($conn, $currentPage, $recordsPerPage);

$ventas = [];
$paginationHtml = ''; // Contendrá los enlaces HTML de paginación.

if ($queryResult !== false) {
    $ventas = $queryResult['data']; // Datos de las ventas.
    $totalRecords = $queryResult['totalRecords']; // Total de registros.
    $totalPages = ceil($totalRecords / $recordsPerPage); // Total de páginas.

    // Generar enlaces de paginación si hay más de una página.
    if ($totalPages > 1) {
        $paginationHtml = generatePaginationLinks($currentPage, $totalPages);
    }
}

/**
 * Genera los enlaces HTML de paginación.
 *
 * @param int $currentPage Página actual.
 * @param int $totalPages Número total de páginas.
 * @param array $baseParams Parámetros base de la URL.
 * @param int $linksToShow Número de enlaces visibles a la vez.
 * @return string HTML con los enlaces de paginación.
 */
function generatePaginationLinks(int $currentPage, int $totalPages, array $baseParams = [], int $linksToShow = 5): string
{
    if ($totalPages <= 1) return '';

    $paginationHtml = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center mt-4">';

    // Botón "Anterior"
    $prevPage = $currentPage - 1;
    $baseParams['page'] = $prevPage;
    $queryStringPrev = http_build_query($baseParams);
    $paginationHtml .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '">';
    $paginationHtml .= '<a class="page-link" href="?' . $queryStringPrev . '">Anterior</a></li>';

    // Rango de páginas visibles
    $startPage = max(1, $currentPage - floor($linksToShow / 2));
    $endPage = min($totalPages, $currentPage + floor($linksToShow / 2));

    // Mostrar "1..." si no se está en las primeras páginas
    if ($startPage > 1) {
        $baseParams['page'] = 1;
        $paginationHtml .= '<li class="page-item"><a class="page-link" href="?' . http_build_query($baseParams) . '">1</a></li>';
        if ($startPage > 2) {
            $paginationHtml .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Generar los enlaces de las páginas intermedias
    for ($i = $startPage; $i <= $endPage; $i++) {
        $baseParams['page'] = $i;
        $queryStringPage = http_build_query($baseParams);
        $paginationHtml .= '<li class="page-item ' . ($i == $currentPage ? 'active' : '') . '">';
        $paginationHtml .= '<a class="page-link" href="?' . $queryStringPage . '">' . $i . '</a></li>';
    }

    // Mostrar "… última" si no se está en las últimas páginas
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $paginationHtml .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $baseParams['page'] = $totalPages;
        $paginationHtml .= '<li class="page-item"><a class="page-link" href="?' . http_build_query($baseParams) . '">' . $totalPages . '</a></li>';
    }

    // Botón "Siguiente"
    $nextPage = $currentPage + 1;
    $baseParams['page'] = $nextPage;
    $queryStringNext = http_build_query($baseParams);
    $paginationHtml .= '<li class="page-item ' . ($currentPage >= $totalPages ? 'disabled' : '') . '">';
    $paginationHtml .= '<a class="page-link" href="?' . $queryStringNext . '">Siguiente</a></li>';

    $paginationHtml .= '</ul></nav>';
    return $paginationHtml;
}
?>

<!-- Contenedor principal -->
<div class="container mt-5 mb-5">
    <h2 class="text-center mb-4">Listado de Ventas</h2>

    <!-- Mensaje de sesión (alerta de éxito o error) -->
    <?php if (isset($_SESSION['message'])) : ?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type']); ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    ?>
    <?php endif; ?>

    <!-- Tabla de ventas -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Vendedor</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ventas)) : ?>
                    <?php foreach ($ventas as $venta) : ?>
                        <tr>
                            <td><?= htmlspecialchars($venta['idVenta']); ?></td>
                            <td><?= htmlspecialchars(date("d/m/Y", strtotime($venta['fechaVenta']))); ?></td>
                            <td><?= htmlspecialchars($venta['vendedor']); ?></td>
                            <td>$<?= htmlspecialchars(number_format($venta['totalVenta'], 2)); ?></td>
                            <td>
                                <!-- Enlace para ver factura en PDF -->
                                <a href="generar_factura.php?id_venta=<?= htmlspecialchars($venta['idVenta']); ?>"
                                   class="btn btn-info btn-sm" target="_blank" title="Ver Factura">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                <!-- Enlace para eliminar venta -->
                                <a href="delete_venta.php?id=<?= htmlspecialchars($venta['idVenta']); ?>"
                                   class="btn btn-danger btn-sm" title="Eliminar Venta"
                                   onclick="return confirm('¿Estás seguro de que quieres eliminar esta venta? Esta acción devolverá los productos al inventario.');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <!-- Mensaje si no hay ventas registradas -->
                    <tr>
                        <td colspan="5" class="text-center">No hay ventas registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Enlaces de paginación -->
    <?= $paginationHtml; ?>

</div>

<?php include("includes/footer.php"); ?>
```

---

## Archivo: `login.php`

```php
<?php
session_start(); // Inicia o reanuda una sesión existente para poder usar variables de sesión (por ejemplo, guardar datos del usuario logueado)
require_once("db.php"); // Incluye el archivo de conexión a la base de datos (debe crear la variable $conn con la conexión activa)

// Verifica si el formulario de login fue enviado
if (isset($_POST["login"])) {

    // Limpieza de datos para evitar ataques de inyección SQL o espacios innecesarios
    $nombreUsuario = trim($_POST["usuario"]);      // Elimina espacios antes y después del nombre de usuario
    $contrasenia = trim($_POST["contrasenia"]);    // Elimina espacios antes y después de la contraseña

    // Validación básica: los campos no deben estar vacíos
    if (empty($nombreUsuario) || empty($contrasenia)) {
        $_SESSION['message'] = 'Por favor, completa todos los campos'; // Mensaje de error para mostrar en la página principal
        $_SESSION['message_type'] = 'warning'; // Tipo de alerta (Bootstrap)
        header("Location: index.php"); // Redirige al login
        exit(); // Detiene la ejecución del script
    }

    // 1️⃣ Consulta SQL para buscar un usuario con el nombre ingresado
    $sql = "SELECT * FROM Usuario WHERE nombreUsuario = ?";
    $stmt = $conn->prepare($sql); // Prepara la consulta para evitar inyecciones SQL
    $stmt->bind_param("s", $nombreUsuario); // Asigna el parámetro (s = string)
    $stmt->execute(); // Ejecuta la consulta
    $result = $stmt->get_result(); // Obtiene el resultado de la consulta

    // Si se encuentra exactamente un usuario con ese nombre
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc(); // Obtiene la fila de datos como arreglo asociativo
        $contrasena_hash = trim($usuario["contrasenia"]); // Obtiene la contraseña en la base de datos (ya hasheada) y elimina espacios

        // 2️⃣ Verifica la contraseña ingresada comparándola con el hash almacenado
        if (password_verify($contrasenia, $contrasena_hash)) {
            // Si la contraseña es válida, se inician variables de sesión
            // ⚠️ Aquí hay un pequeño error: 'id_usuario' debería guardar el ID del usuario, no el tipo.
            $_SESSION['id_usuario'] = $usuario['tipoUsuario']; // Guarda el tipo de usuario (probablemente Admin, Vendedor, etc.)
            $_SESSION['nombreUsuario'] = $nombreUsuario; // Guarda el nombre del usuario logueado

            // Redirige al panel principal después de un login exitoso
            header("Location: dashboard.php");
            exit();
        } else {
            // Si la contraseña no coincide
            $_SESSION['message'] = 'Credenciales incorrectas';
            $_SESSION['message_type'] = 'danger';
            header("Location: index.php"); // Redirige al login
            exit();
        }
    } else {
        // Si no se encuentra el usuario
        $_SESSION['message'] = 'Credenciales IncorrectasU'; // Mensaje de error (la "U" parece un error de tipeo)
        $_SESSION['message_type'] = 'danger';
        header("Location: index.php");
        exit();
    }

    // Cierra los recursos usados
    $stmt->close();
    $conn->close();
}
?>
```

---

## Archivo: `logout.php`

```php
<?php
session_start();  // Asegúrate de que esta línea esté al principio de tu script

session_unset();  // Elimina todas las variables de sesión
session_destroy(); // Destruye la sesión
// Redirige al login
header("Location: index.php");
exit();
?>
```

---

## Archivo: `productos.php`

```php
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
```

---

## Archivo: `queries/dashboard_querie.php`

```php
<?php
// =======================================================
// Archivo: queries/dashboard_querie.php
// Descripción: Contiene todas las funciones utilizadas en el panel de control (dashboard)
// para obtener información de productos, proveedores, ventas, entradas y estadísticas.
// =======================================================


/**
 * Obtiene una lista de productos filtrados por nombre, proveedor o categoría,
 * con soporte para paginación.
 *
 * @param mysqli $conn Conexión a la base de datos.
 * @param string $filter Texto a buscar (opcional).
 * @param int $page Número de página actual.
 * @param int $recordsPerPage Cantidad de registros por página.
 * @return array|false Devuelve un arreglo con los datos y el total de registros o false en caso de error.
 */
function getFilteredProducts(mysqli $conn, string $filter = '', int $page = 1, int $recordsPerPage = 10): array|false
{
    $searchTerm = "%" . $filter . "%"; // Prepara el término de búsqueda para LIKE
    $offset = ($page - 1) * $recordsPerPage;

    // Consulta para contar el total de registros coincidentes
    $sqlCount = "SELECT COUNT(P.idProducto) as total 
                 FROM Productos P 
                 LEFT JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor 
                 LEFT JOIN Categorias C ON P.CategoriaId = C.idCategoria 
                 WHERE P.nombreProducto LIKE ? OR Pr.nombreProveedor LIKE ? OR C.nombreCategoria LIKE ?";
    $stmtCount = $conn->prepare($sqlCount);
    if (!$stmtCount) { error_log("Error prepare count: " . $conn->error); return false; }
    $stmtCount->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
    if (!$stmtCount->execute()) { $stmtCount->close(); return false; }
    $totalRecords = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;
    $stmtCount->close();

    // Consulta para obtener los datos de los productos filtrados
    $sqlData = "SELECT P.idProducto, P.nombreProducto, P.descripcionProducto, P.cantidad, 
                       P.precioVenta, P.precioCompra, Pr.nombreProveedor, C.nombreCategoria 
                FROM Productos P 
                LEFT JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor 
                LEFT JOIN Categorias C ON P.CategoriaId = C.idCategoria 
                WHERE P.nombreProducto LIKE ? OR Pr.nombreProveedor LIKE ? OR C.nombreCategoria LIKE ? 
                ORDER BY P.idProducto DESC 
                LIMIT ? OFFSET ?";
    $stmtData = $conn->prepare($sqlData);
    if (!$stmtData) { error_log("Error prepare data: " . $conn->error); return false; }
    $stmtData->bind_param('sssii', $searchTerm, $searchTerm, $searchTerm, $recordsPerPage, $offset);
    if (!$stmtData->execute()) { $stmtData->close(); return false; }
    $resultData = $stmtData->get_result();
    $data = $resultData ? $resultData->fetch_all(MYSQLI_ASSOC) : [];
    $stmtData->close();

    return ['data' => $data, 'totalRecords' => (int)$totalRecords];
}


/**
 * Obtiene todos los proveedores con paginación.
 */
function getAllProviders(mysqli $conn, int $page = 1, int $recordsPerPage = 10): array|false
{
    $offset = ($page - 1) * $recordsPerPage;

    // Cuenta total de proveedores
    $sqlCount = "SELECT COUNT(*) as total FROM Proveedores";
    $resultCount = $conn->query($sqlCount);
    $totalRecords = $resultCount ? $resultCount->fetch_assoc()['total'] : 0;

    // Consulta los datos de los proveedores
    $sqlData = "SELECT idProveedor, nombreProveedor, descripcionProveedor, direccionProveedor, 
                       telefono, Correo, infoAdicional 
                FROM Proveedores 
                ORDER BY idProveedor DESC 
                LIMIT ? OFFSET ?";
    $stmtData = $conn->prepare($sqlData);
    if (!$stmtData) { return false; }
    $stmtData->bind_param('ii', $recordsPerPage, $offset);
    if (!$stmtData->execute()) { $stmtData->close(); return false; }
    $resultData = $stmtData->get_result();
    $data = $resultData ? $resultData->fetch_all(MYSQLI_ASSOC) : [];
    $stmtData->close();

    return ['data' => $data, 'totalRecords' => (int)$totalRecords];
}


/**
 * Obtiene los productos más vendidos (para gráficos o reportes).
 */
function getTopSellingProducts(mysqli $conn, int $limit = 7): array|false
{
    $limit = max(1, $limit);
    $sql = "SELECT P.nombreProducto, SUM(DV.cantidad) AS total_vendido 
            FROM detalle_venta DV 
            JOIN productos P ON DV.productoId = P.idProducto 
            GROUP BY P.idProducto, P.nombreProducto 
            ORDER BY total_vendido DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { return false; }
    $stmt->bind_param('i', $limit);
    if (!$stmt->execute()) { $stmt->close(); return false; }
    $result = $stmt->get_result();
    $products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $products;
}


/**
 * Obtiene los productos con menor cantidad en stock.
 */
function getLowStockProducts(mysqli $conn, int $limit = 7): array|false
{
    $limit = max(1, $limit);
    $sql = "SELECT nombreProducto, cantidad 
            FROM Productos 
            ORDER BY cantidad ASC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { return false; }
    $stmt->bind_param('i', $limit);
    if (!$stmt->execute()) { $stmt->close(); return false; }
    $result = $stmt->get_result();
    $products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $products;
}


/**
 * Genera los datos de ventas agrupadas por día para los últimos "n" días,
 * útiles para graficar estadísticas.
 */
function getSalesDataForChart(mysqli $conn, int $days): array
{
    $labels = [];
    $salesData = [];

    // Inicializa los días con valor 0
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = $date;
        $salesData[$date] = 0;
    }

    // Rango de fechas a consultar
    $startDate = date('Y-m-d', strtotime("-($days - 1) days"));
    $endDate = date('Y-m-d');

    $sql = "SELECT DATE(fechaVenta) AS fecha, SUM(totalVenta) AS total_ventas 
            FROM ventas 
            WHERE DATE(fechaVenta) BETWEEN ? AND ? 
            GROUP BY DATE(fechaVenta)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('ss', $startDate, $endDate);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                if (isset($salesData[$row['fecha']])) {
                    $salesData[$row['fecha']] = $row['total_ventas'];
                }
            }
        }
        $stmt->close();
    }

    return ['labels' => $labels, 'data' => array_values($salesData)];
}


/**
 * Obtiene productos junto con información de sus proveedores.
 */
function getProductsWithProviders(mysqli $conn, int $page = 1, int $recordsPerPage = 10): array|false
{
    $offset = ($page - 1) * $recordsPerPage;

    $sqlCount = "SELECT COUNT(P.idProducto) as total 
                 FROM Productos P 
                 INNER JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor";
    $resCount = $conn->query($sqlCount);
    $totalRecords = $resCount ? $resCount->fetch_assoc()['total'] : 0;

    $sql = "SELECT P.nombreProducto, P.cantidad, Pr.nombreProveedor, Pr.telefono, Pr.Correo 
            FROM Productos P 
            INNER JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor 
            ORDER BY P.idProducto DESC 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if(!$stmt) return false;
    $stmt->bind_param('ii', $recordsPerPage, $offset);
    if(!$stmt->execute()) return false;
    $result = $stmt->get_result();
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return ['data' => $data, 'totalRecords' => (int)$totalRecords];
}


/**
 * Obtiene las entradas de productos (compras a proveedores).
 */
function getAllEntries(mysqli $conn, int $page = 1, int $recordsPerPage = 10): array|false
{
    $offset = ($page - 1) * $recordsPerPage;

    $sqlCount = "SELECT COUNT(*) as total FROM EntradaProductos";
    $resCount = $conn->query($sqlCount);
    $totalRecords = $resCount ? $resCount->fetch_assoc()['total'] : 0;

    $sql = "SELECT E.idEntrada, E.fechaEntrada, P.nombreProducto, E.cantidadComprada, 
                   E.precioCompraUnidad, Pr.nombreProveedor 
            FROM EntradaProductos E 
            LEFT JOIN Productos P ON E.productoId = P.idProducto 
            LEFT JOIN Proveedores Pr ON E.proveedorId = Pr.idProveedor 
            ORDER BY E.idEntrada DESC 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if(!$stmt) return false;
    $stmt->bind_param('ii', $recordsPerPage, $offset);
    if(!$stmt->execute()) return false;
    $result = $stmt->get_result();
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return ['data' => $data, 'totalRecords' => (int)$totalRecords];
}


/**
 * Obtiene las ventas recientes realizadas por los vendedores.
 */
function getRecentSales(mysqli $conn, int $page = 1, int $recordsPerPage = 10): array|false
{
    $offset = ($page - 1) * $recordsPerPage;

    $sqlCount = "SELECT COUNT(*) as total FROM ventas";
    $resCount = $conn->query($sqlCount);
    $totalRecords = $resCount ? $resCount->fetch_assoc()['total'] : 0;

    $sql = "SELECT V.idVenta, V.fechaVenta, V.totalVenta, Vd.nombreCompleto 
            FROM ventas V 
            LEFT JOIN usuario Vd ON V.vendedorId = Vd.idUsuario 
            ORDER BY V.idVenta DESC 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { return false; }
    $stmt->bind_param('ii', $recordsPerPage, $offset);
    if (!$stmt->execute()) { $stmt->close(); return false; }
    $result = $stmt->get_result();
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return ['data' => $data, 'totalRecords' => (int)$totalRecords];
}

?>
```

---

## Archivo: `queries/informe_queries.php`

```php
<?php
// =======================================================
// Archivo: queries/reportes_ventas.php
// Descripción: Contiene funciones para generar reportes de ventas
// por rango de fechas y obtener información de los vendedores.
// =======================================================


/**
 * Obtiene un reporte de ventas dentro de un rango de fechas, con la opción
 * de filtrar por un vendedor específico.
 *
 * @param mysqli $conn Conexión activa a la base de datos MySQL.
 * @param string $startDate Fecha de inicio del rango (formato 'YYYY-MM-DD').
 * @param string $endDate Fecha de fin del rango (formato 'YYYY-MM-DD').
 * @param int|null $vendedorId (Opcional) ID del vendedor para filtrar el reporte.
 *
 * @return array Arreglo asociativo con los datos del reporte, donde cada elemento
 * incluye la fecha de venta, el nombre del vendedor, el número de ventas y el total vendido.
 */
function getSalesReportByDateRange($conn, $startDate, $endDate, $vendedorId = null) {
    // Si se proporciona un ID de vendedor, se filtra por ese usuario específico
    if ($vendedorId) {
        $query = "SELECT v.fechaVenta, u.nombrecompleto, 
                         COUNT(DISTINCT v.idVenta) AS numero_ventas, 
                         SUM(v.totalVenta) AS total 
                  FROM ventas v
                  INNER JOIN Usuario u ON v.vendedorId = u.IdUsuario
                  WHERE v.fechaVenta BETWEEN ? AND ? AND v.vendedorId = ?
                  GROUP BY v.fechaVenta, u.nombrecompleto
                  ORDER BY v.fechaVenta ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $startDate, $endDate, $vendedorId);
    } else {
        // Si no se especifica vendedor, se muestran todas las ventas del rango
        $query = "SELECT v.fechaVenta, u.nombrecompleto, 
                         COUNT(DISTINCT v.idVenta) AS numero_ventas, 
                         SUM(v.totalVenta) AS total 
                  FROM ventas v
                  INNER JOIN Usuario u ON v.vendedorId = u.IdUsuario
                  WHERE v.fechaVenta BETWEEN ? AND ?
                  GROUP BY v.fechaVenta, u.nombrecompleto
                  ORDER BY v.fechaVenta ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $startDate, $endDate);
    }

    // Ejecuta la consulta
    $stmt->execute();
    $result = $stmt->get_result();

    // Convierte los resultados en un arreglo asociativo
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Devuelve los datos del reporte
    return $data;
}


/**
 * Obtiene todos los usuarios (vendedores) registrados en la base de datos.
 * 
 * @param mysqli $conn Conexión activa a la base de datos MySQL.
 * @return array Arreglo asociativo con los campos IdUsuario y nombrecompleto de cada usuario.
 */
function getAllUsuarios($conn) {
    $result = $conn->query("SELECT IdUsuario, nombrecompleto FROM Usuario");
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
```

---

## Archivo: `queries/venta_querie.php`

```php
<?php
function getAllVentas($conn, int $page = 1, int $recordsPerPage = 10): array|false {
    $offset = ($page - 1) * $recordsPerPage;

    // Contar total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM ventas";
    $resCount = $conn->query($sqlCount);
    if (!$resCount) {
        error_log("Error en getAllVentas (count): " . $conn->error);
        return false;
    }
    $totalRecords = $resCount->fetch_assoc()['total'] ?? 0;

    // Obtener las ventas de la página actual
    $sql = "SELECT v.idVenta, v.fechaVenta, u.nombrecompleto AS vendedor, v.totalVenta
            FROM ventas v
            INNER JOIN usuario u ON v.vendedorId = u.idUsuario
            ORDER BY v.fechaVenta DESC, v.idVenta DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error en getAllVentas (prepare): " . $conn->error);
        return false;
    }

    $stmt->bind_param('ii', $recordsPerPage, $offset);
    if (!$stmt->execute()) {
        error_log("Error en getAllVentas (execute): " . $stmt->error);
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return ['data' => $data, 'totalRecords' => (int)$totalRecords];
}

function getVentaDetailsById($conn, $idVenta) {
    $details = [];

    // Información principal
    $query_main = "SELECT v.idVenta, v.fechaVenta, u.nombrecompleto AS vendedor, v.totalVenta
                   FROM ventas v
                   INNER JOIN usuario u ON v.vendedorId = u.idUsuario
                   WHERE v.idVenta = ?";

    $stmt_main = $conn->prepare($query_main);
    if (!$stmt_main) {
        error_log("Error preparando getVentaDetailsById: " . $conn->error);
        return null;
    }

    $stmt_main->bind_param("i", $idVenta);
    $stmt_main->execute();
    $result_main = $stmt_main->get_result();
    $details['venta'] = $result_main->fetch_assoc();

    if (!$details['venta']) return null; // No existe la venta

    // Detalle de productos
    $query_items = "SELECT p.nombreProducto, dv.cantidad, dv.precioUnitario,
                           (dv.cantidad * dv.precioUnitario) AS importe
                    FROM detalle_venta dv
                    INNER JOIN productos p ON dv.productoId = p.idProducto
                    WHERE dv.ventaId = ?";

    $stmt_items = $conn->prepare($query_items);
    if (!$stmt_items) {
        error_log("Error preparando detalle de venta: " . $conn->error);
        return null;
    }

    $stmt_items->bind_param("i", $idVenta);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    $details['items'] = $result_items ? $result_items->fetch_all(MYSQLI_ASSOC) : [];

    foreach ($details['items'] as &$item) {
        $item['cantidadVendida'] = $item['cantidad'];
        unset($item['cantidad']);
    }

    return $details;
}
?>
```

---

## Archivo: `save_entrada.php`

```php
<?php
/**
 * save_entrada.php
 *
 * Descripción:
 * Este script se encarga de procesar el formulario de registro de nuevas entradas de productos.
 * Inserta los datos en la tabla `EntradaProductos` y actualiza el stock en la tabla `Productos`.
 */

// Incluye el archivo de conexión a la base de datos
include("db.php");

// Verifica si el formulario fue enviado con el botón "save_entrada"
if (isset($_POST['save_entrada'])) {

    // Captura los datos enviados desde el formulario
    $fechaEntrada = $_POST['fechaEntrada'];             // Fecha en la que se realiza la entrada del producto
    $productoId = $_POST['productoId'];                 // ID del producto ingresado
    $cantidadComprada = $_POST['cantidadComprada'];     // Cantidad comprada del producto
    $precioCompraUnidad = $_POST['precioCompraUnidad']; // Precio de compra por unidad
    $proveedorId = $_POST['proveedorId'];               // ID del proveedor asociado

    // Inserta la nueva entrada en la tabla "EntradaProductos"
    $query = "INSERT INTO EntradaProductos (fechaEntrada, productoId, cantidadComprada, precioCompraUnidad, proveedorId) 
              VALUES ('$fechaEntrada', '$productoId', '$cantidadComprada', '$precioCompraUnidad', '$proveedorId')";
    $result = mysqli_query($conn, $query); // Ejecuta la consulta

    // Si ocurre un error en la inserción, se detiene el script y muestra el error
    if (!$result) {
        die("Query Failed: " . mysqli_error($conn));
    }

    // Actualiza la cantidad disponible del producto sumando la cantidad ingresada
    $query_update = "UPDATE Productos SET cantidad = cantidad + $cantidadComprada WHERE idProducto = '$productoId'";
    $result_update = mysqli_query($conn, $query_update); // Ejecuta la actualización

    // Si ocurre un error al actualizar el stock, se detiene el script y muestra el error
    if (!$result_update) {
        die("Query Failed: " . mysqli_error($conn));
    }

    // Define un mensaje de éxito que se mostrará en el dashboard
    $_SESSION['message'] = 'Entrada registrada y cantidad actualizada correctamente';
    $_SESSION['message_type'] = 'success';

    // Redirige al panel principal después de guardar los datos
    header("Location: dashboard.php");
}
?>
```

---

## Archivo: `save_producto.php`

```php
<?php
/**
 * save_producto.php
 *
 * Descripción:
 * Este script procesa el formulario de registro de nuevos productos y los guarda en la base de datos.
 * Utiliza consultas preparadas para evitar inyecciones SQL.
 */

// Verifica si la solicitud fue enviada mediante el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    echo "Guardando DATOS"; // Mensaje de depuración para confirmar recepción de datos

    // Incluye el archivo de conexión a la base de datos
    include("db.php");

    // Captura los datos enviados desde el formulario
    $nombreProducto = $_POST['nombreProducto'];           // Nombre del producto
    $descripcionProducto = $_POST['descripcionProducto']; // Descripción del producto
    $cantidad = $_POST['cantidad'];                       // Cantidad en inventario
    $precioVenta = $_POST['precioVenta'];                 // Precio de venta al público
    $precioCompra = $_POST['precioCompra'];               // Precio de compra al proveedor
    $proveedorId = $_POST['proveedorId'];                 // ID del proveedor asociado
    $CategoriaId = $_POST['CategoriaId'];                 // ID de la categoría del producto

    // Prepara la sentencia SQL para insertar los datos en la tabla "Productos"
    $sql = "INSERT INTO Productos (nombreProducto, descripcionProducto, cantidad, precioVenta, precioCompra, proveedorId, CategoriaId)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Prepara la consulta para evitar inyecciones SQL
    $stmt = $conn->prepare($sql);

    // Asocia los valores a los parámetros de la consulta
    // s = string, i = integer, d = double
    $stmt->bind_param("ssiddii", $nombreProducto, $descripcionProducto, $cantidad, $precioVenta, $precioCompra, $proveedorId, $CategoriaId);

    // Ejecuta la consulta y verifica si fue exitosa
    if ($stmt->execute()) {
        // Si se guarda correctamente, define un mensaje de éxito y redirige al dashboard
        $_SESSION['message'] = 'producto ingresado correctamente';
        $_SESSION['message_type'] = 'success';
        header("location: dashboard.php");
    } else {
        // Si ocurre un error, muestra información de depuración
        echo "Error:<hr> " . $sql . "<hr>" . $conn->error;
    }

    // Cierra la consulta y la conexión
    $stmt->close();
    $conn->close();

} else {
    // Si el script se ejecuta sin recibir datos POST, muestra mensaje de error
    echo "Error, datos no recibidos aaaaaaaaaaaaaa";
}
?>
```

---

## Archivo: `save_proveedor.php`

```php
<?php
/**
 * save_proveedor.php
 *
 * Descripción:
 * Este script recibe los datos del formulario de creación de un nuevo proveedor,
 * los valida y los inserta en la base de datos mediante una consulta preparada.
 * También redirige al panel principal si la operación fue exitosa.
 */

// Verifica si el formulario fue enviado correctamente
if (isset($_POST['save_proveedor'])) {
    
    echo "Guardando DATOS"; // Mensaje de depuración para confirmar que los datos llegaron

    // Incluye la conexión a la base de datos
    include("db.php");

    // Captura los valores enviados desde el formulario
    $nombreProveedor = $_POST["nombreProveedor"];           // Nombre del proveedor
    $descripcionProveedor = $_POST["descripcionProveedor"]; // Descripción del proveedor
    $direccionProveedor = $_POST["direccionProveedor"];     // Dirección física del proveedor
    $telefono = $_POST["telefono"];                         // Teléfono de contacto
    $Correo = $_POST["Correo"];                             // Correo electrónico
    $infoAdicional = $_POST["infoAdicional"];               // Información adicional (opcional)

    // Sentencia SQL para insertar los datos en la tabla "Proveedores"
    $sql = "INSERT INTO Proveedores (nombreProveedor, descripcionProveedor, direccionProveedor, telefono, Correo, infoAdicional)
            VALUES (?,?,?,?,?,?)";

    // Prepara la consulta para evitar inyecciones SQL
    $stmt = $conn->prepare($sql);

    // Asocia los valores a los parámetros de la consulta (todos tipo string)
    $stmt->bind_param("ssssss", $nombreProveedor, $descripcionProveedor, $direccionProveedor, $telefono, $Correo, $infoAdicional);

    // Ejecuta la consulta
    if ($stmt->execute()) {
        // Si se guarda correctamente, redirige al dashboard
        header("location: dashboard.php");
    } else {
        // Si ocurre un error, muestra información detallada
        echo "Error:<hr> " . $sql . "<hr>" . $conn->error;
    }

    // Cierra la consulta y la conexión
    $stmt->close();
    $conn->close();

} else {
    // Si no se recibieron datos, muestra un mensaje de error
    echo "Error, datos no recibidos aaaaaaaaaaaaaa";
}
?>
```

---

## Archivo: `save_venta.php`

```php
<?php
/**
 * save_venta.php
 * 
 * Este archivo se encarga de procesar y guardar una nueva venta en la base de datos.
 * Incluye la inserción del registro principal en la tabla `ventas`, los detalles en 
 * la tabla `detalle_venta`, y la actualización del stock de productos.
 * 
 * Autor: [Tu nombre o equipo]
 * Fecha: [Coloca la fecha actual]
 */

ini_set('display_errors', 1); // Muestra errores en pantalla (útil para depuración)
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); // Reporta todos los errores posibles

include("db.php"); // Conexión a la base de datos

// Verifica si el formulario fue enviado correctamente
if (isset($_POST['save_venta'])) {

    // Inicia sesión solo si aún no hay una activa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Captura los datos del formulario
    $fechaVenta = $_POST['fechaVenta'];
    $vendedorId = $_POST['vendedorId'];
    $totalVenta = $_POST['precioVentaTotal'];
    $productosIds = $_POST['productoId']; // Arreglo con los IDs de los productos vendidos
    $cantidades = $_POST['cantidadVenta']; // Arreglo con las cantidades vendidas

    // Inicia una transacción para asegurar que todas las operaciones se completen correctamente
    mysqli_begin_transaction($conn);

    try {
        /**
         * 1️⃣ Insertar la venta principal en la tabla `ventas`
         */
        $query_venta = "INSERT INTO ventas (fechaVenta, vendedorId, totalVenta) VALUES (?, ?, ?)";
        $stmt_venta = mysqli_prepare($conn, $query_venta);
        mysqli_stmt_bind_param($stmt_venta, "sid", $fechaVenta, $vendedorId, $totalVenta);

        // Ejecuta la inserción y verifica errores
        if (!mysqli_stmt_execute($stmt_venta)) {
            throw new Exception("Error al guardar la venta principal: " . mysqli_stmt_error($stmt_venta));
        }

        // Obtiene el ID autogenerado de la venta recién insertada
        $ventaId = mysqli_insert_id($conn);

        /**
         * 2️⃣ Recorrer cada producto vendido e insertar su detalle
         */
        foreach ($productosIds as $index => $productoId) {
            $cantidad = $cantidades[$index];

            // Obtiene el precio actual y stock disponible del producto
            $query_prod_info = "SELECT precioVenta, cantidad FROM productos WHERE idProducto = ? FOR UPDATE";
            $stmt_prod_info = mysqli_prepare($conn, $query_prod_info);
            mysqli_stmt_bind_param($stmt_prod_info, "i", $productoId);
            mysqli_stmt_execute($stmt_prod_info);
            $result_prod_info = mysqli_stmt_get_result($stmt_prod_info);
            $prod_info = mysqli_fetch_assoc($result_prod_info);

            // Almacena el precio unitario y el stock disponible
            $precioUnitario = $prod_info['precioVenta'];
            $stockDisponible = $prod_info['cantidad'];

            // Verifica si hay suficiente stock para completar la venta
            if ($cantidad > $stockDisponible) {
                throw new Exception("No hay suficiente stock para el producto ID $productoId. Disponible: $stockDisponible, Solicitado: $cantidad");
            }

            // Inserta el detalle de la venta (producto, cantidad y precio)
            $query_detalle = "INSERT INTO detalle_venta (ventaId, productoId, cantidad, precioUnitario) VALUES (?, ?, ?, ?)";
            $stmt_detalle = mysqli_prepare($conn, $query_detalle);
            mysqli_stmt_bind_param($stmt_detalle, "iiid", $ventaId, $productoId, $cantidad, $precioUnitario);
            if (!mysqli_stmt_execute($stmt_detalle)) {
                throw new Exception("Error al guardar el detalle del producto: " . mysqli_stmt_error($stmt_detalle));
            }

            // Actualiza el stock restando la cantidad vendida
            $query_update_stock = "UPDATE productos SET cantidad = cantidad - ? WHERE idProducto = ?";
            $stmt_update_stock = mysqli_prepare($conn, $query_update_stock);
            mysqli_stmt_bind_param($stmt_update_stock, "ii", $cantidad, $productoId);
            if (!mysqli_stmt_execute($stmt_update_stock)) {
                throw new Exception("Error al actualizar el stock: " . mysqli_stmt_error($stmt_update_stock));
            }
        }

        /**
         * 3️⃣ Si todo salió bien, confirmar la transacción
         */
        mysqli_commit($conn);

        // Guarda mensaje de éxito en la sesión
        $_SESSION['message'] = 'Venta registrada correctamente con ID: ' . $ventaId;
        $_SESSION['message_type'] = 'success';

        // Redirige al listado de ventas
        header("Location: listado_ventas.php");

    } catch (Exception $e) {
        /**
         * Si ocurre un error en cualquier paso, se revierte todo (rollback)
         */
        mysqli_rollback($conn);
        $_SESSION['message'] = 'Error al registrar la venta: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        header("Location: ventas.php");
    }

} else {
    // Si el formulario no fue enviado, redirige a la página principal de ventas
    header("Location: ventas.php");
}
?>
```

---

## Archivo: `ventas.php`

```php
<?php
/**
 * registrar_venta.php
 * 
 * Este archivo muestra el formulario para registrar una nueva venta.
 * Se encarga de obtener los datos de productos y vendedores desde la base de datos,
 * y genera dinámicamente los campos para seleccionar productos, cantidades y calcular el total.
 * 
 * Autor: [Tu nombre o equipo]
 * Fecha: [Fecha actual]
 */

// Incluye el archivo de conexión a la base de datos
include("db.php");

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =======================================================
   🔹 OBTENER LISTA DE PRODUCTOS DESDE LA BASE DE DATOS
   ======================================================= */
$productos = [];

$query_productos = "SELECT idProducto, nombreProducto, precioVenta FROM Productos ORDER BY nombreProducto ASC";
$result_productos_query = mysqli_query($conn, $query_productos);

if ($result_productos_query) {
    // Devuelve todos los productos como arreglo asociativo
    $productos = mysqli_fetch_all($result_productos_query, MYSQLI_ASSOC);
} else {
    // Si ocurre un error al consultar, se registra en el log del servidor
    error_log("Error al obtener productos: " . mysqli_error($conn));
    $_SESSION['message'] = 'Error crítico: No se pudo cargar la lista de productos.';
    $_SESSION['message_type'] = 'danger';
}

/* =======================================================
   🔹 OBTENER LISTA DE USUARIOS (VENDEDORES)
   ======================================================= */
$usuarios = [];

$query_usuarios = "SELECT idUsuario, nombreCompleto FROM usuario ORDER BY nombreCompleto ASC";
$result_usuarios_query = mysqli_query($conn, $query_usuarios);

if ($result_usuarios_query) {
    $usuarios = mysqli_fetch_all($result_usuarios_query, MYSQLI_ASSOC);
} else {
    error_log("Error al obtener usuarios: " . mysqli_error($conn));
}

// Incluye la cabecera de la página
include("includes/header.php");
?>

<!-- =======================================================
     🧾 FORMULARIO PARA REGISTRAR NUEVA VENTA
     ======================================================= -->
<div class="container mt-5">
    <h2 class="mb-4 text-center">Registrar Nueva Venta</h2>

    <!-- Mostrar mensajes de sesión (éxito o error) -->
    <?php if (isset($_SESSION['message'])) : ?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type']); ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    ?>
    <?php endif; ?>

    <!-- Formulario principal -->
    <form action="save_venta.php" method="POST" id="form-registrar-venta">

        <!-- Información general de la venta -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="fechaVenta" class="form-label">Fecha de Venta <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="fechaVenta" name="fechaVenta" value="<?= date('Y-m-d'); ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="vendedorId" class="form-label">Vendedor <span class="text-danger">*</span></label>
                <select class="form-select" id="vendedorId" name="vendedorId" required>
                    <option value="">Selecciona un vendedor</option>
                    <?php if (!empty($usuarios)) : ?>
                        <?php foreach ($usuarios as $usuario) : ?>
                            <option value="<?php echo htmlspecialchars($usuario['idUsuario']); ?>">
                                <?php echo htmlspecialchars($usuario['nombreCompleto']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <option value="" disabled>No hay vendedores disponibles</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <hr>

        <!-- Sección de productos -->
        <h4 class="mb-3">Productos</h4>
        <div id="productos-container">
            <!-- Plantilla base de producto -->
            <div class="producto-item row align-items-end mb-3 p-3 border rounded bg-light">
                <div class="col-md-5">
                    <label class="form-label">Producto <span class="text-danger">*</span></label>
                    <select class="form-select producto-select" name="productoId[]" required>
                        <option value="">Selecciona un producto</option>
                        <?php if (!empty($productos)) : ?>
                            <?php foreach ($productos as $producto) : ?>
                                <option value="<?php echo htmlspecialchars($producto['idProducto']); ?>" 
                                        data-precio="<?php echo htmlspecialchars($producto['precioVenta'] ?? '0'); ?>">
                                    <?php echo htmlspecialchars($producto['nombreProducto']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <option value="" disabled>No hay productos disponibles</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="number" class="form-control cantidad-venta" name="cantidadVenta[]" min="1" required placeholder="Ej: 1">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Precio U.</label>
                    <input type="text" class="form-control precio-unitario bg-white" name="precioUnitario[]" placeholder="$0.00" readonly>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-product-btn" style="display:none;">Eliminar</button>
                </div>
            </div>
        </div>

        <!-- Botón para agregar más productos -->
        <button type="button" class="btn btn-primary mb-3" id="add-product-btn">
            <i class="fas fa-plus"></i> Agregar Otro Producto
        </button>
        
        <hr>

        <!-- Total de la venta -->
        <div class="row justify-content-end">
            <div class="col-md-4 text-end">
                <h4>Total Venta: <span id="total-venta-display" class="fw-bold">$0.00</span></h4>
                <input type="hidden" name="precioVentaTotal" id="precioVentaTotalInput" value="0">
            </div>
        </div>

        <!-- Botón principal para registrar -->
        <div class="d-grid gap-2 mt-4">
            <input type="submit" class="btn btn-success btn-lg" name="save_venta" value="Registrar Venta">
        </div>
    </form>
</div>

<!-- =======================================================
     🧠 SCRIPT PARA GESTIONAR LOS PRODUCTOS Y CÁLCULOS DINÁMICOS
     ======================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productosContainer = document.getElementById('productos-container');
    const addProductBtn = document.getElementById('add-product-btn');
    
    // Convierte los productos de PHP a un objeto JS para acceder fácilmente por ID
    const productosDataPHP = <?php echo json_encode($productos); ?> || [];
    const productosData = {};
    productosDataPHP.forEach(p => {
        productosData[p.idProducto] = p;
    });

    // Controla la visibilidad del botón "Eliminar" en cada producto
    function updateRemoveButtonsVisibility() {
        const items = productosContainer.querySelectorAll('.producto-item');
        items.forEach((item, index) => {
            const removeBtn = item.querySelector('.remove-product-btn');
            if (removeBtn) {
                removeBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
            }
        });
    }

    // Calcula los totales de la venta según los productos y cantidades seleccionados
    function calculateAndUpdateTotals() {
        let granTotal = 0;
        productosContainer.querySelectorAll('.producto-item').forEach(item => {
            const productoSelect = item.querySelector('.producto-select');
            const cantidadInput = item.querySelector('.cantidad-venta');
            const precioUnitarioInput = item.querySelector('.precio-unitario');
            
            const productoId = productoSelect.value;
            const cantidad = parseInt(cantidadInput.value) || 0;
            let precioUnitario = 0;

            if (productoId && productosData[productoId] && typeof productosData[productoId].precioVenta !== 'undefined') {
                precioUnitario = parseFloat(productosData[productoId].precioVenta);
                if (isNaN(precioUnitario)) {
                    precioUnitario = 0;
                    precioUnitarioInput.value = 'Error Precio';
                } else {
                    precioUnitarioInput.value = '$' + precioUnitario.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            } else {
                precioUnitarioInput.value = (productoId) ? 'Precio ND' : '$0.00';
            }
            
            granTotal += cantidad * precioUnitario;
        });
        
        // Actualiza la visualización del total y el campo oculto del formulario
        document.getElementById('total-venta-display').textContent = '$' + granTotal.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('precioVentaTotalInput').value = granTotal.toFixed(2);
    }

    // Añade los eventos de interacción a cada ítem de producto
    function addProductItemEventListeners(item) {
        const productoSelect = item.querySelector('.producto-select');
        const cantidadInput = item.querySelector('.cantidad-venta');
        const removeBtn = item.querySelector('.remove-product-btn');

        productoSelect.addEventListener('change', calculateAndUpdateTotals);
        cantidadInput.addEventListener('input', calculateAndUpdateTotals);
        
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                item.remove();
                updateRemoveButtonsVisibility();
                calculateAndUpdateTotals();
            });
        }
    }

    // Duplicar un bloque de producto cuando el usuario agrega otro
    addProductBtn.addEventListener('click', () => {
        const firstItem = productosContainer.querySelector('.producto-item');
        if (!firstItem) return;

        const newItem = firstItem.cloneNode(true);
        
        // Limpia los valores del nuevo ítem
        newItem.querySelector('select.producto-select').value = '';
        newItem.querySelector('input.cantidad-venta').value = '';
        newItem.querySelector('input.precio-unitario').value = '$0.00';
        
        addProductItemEventListeners(newItem);
        
        productosContainer.appendChild(newItem);
        updateRemoveButtonsVisibility();
        calculateAndUpdateTotals(); 
    });
    
    // Inicializa eventos y cálculos
    productosContainer.querySelectorAll('.producto-item').forEach(item => {
        addProductItemEventListeners(item);
    });

    updateRemoveButtonsVisibility();
    calculateAndUpdateTotals();
});
</script>

<?php include("includes/footer.php") ?>
```
