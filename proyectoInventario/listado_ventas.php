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
