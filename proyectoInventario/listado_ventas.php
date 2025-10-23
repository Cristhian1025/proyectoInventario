<?php
include("db.php");
require_once 'queries/venta_querie.php';
include("includes/header.php");

// Lógica de paginación y búsqueda
$recordsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

$searchCedulaNit = $_GET['search_cedula'] ?? null;
$searchNombreCliente = $_GET['search_nombre'] ?? null;

$queryResult = getAllVentas($conn, $currentPage, $recordsPerPage, $searchCedulaNit, $searchNombreCliente);

$ventas = [];
$paginationHtml = '';

if ($queryResult !== false) {
    $ventas = $queryResult['data'];
    $totalRecords = $queryResult['totalRecords'];
    $totalPages = ceil($totalRecords / $recordsPerPage);
    if ($totalPages > 1) {
        $paginationHtml = generatePaginationLinks($currentPage, $totalPages, ['search_cedula' => $searchCedulaNit, 'search_nombre' => $searchNombreCliente]);
    }
}

// Función para generar los enlaces de paginación (copiada de dashboard.php)
function generatePaginationLinks(int $currentPage, int $totalPages, array $baseParams = [], int $linksToShow = 5): string
{
    if ($totalPages <= 1) return '';
    $paginationHtml = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center mt-4">';
    $prevPage = $currentPage - 1;
    $baseParams['page'] = $prevPage;
    $queryStringPrev = http_build_query($baseParams);
    $paginationHtml .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '">';
    $paginationHtml .= '<a class="page-link" href="?' . $queryStringPrev . '">Anterior</a></li>';
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
    $nextPage = $currentPage + 1;
    $baseParams['page'] = $nextPage;
    $queryStringNext = http_build_query($baseParams);
    $paginationHtml .= '<li class="page-item ' . ($currentPage >= $totalPages ? 'disabled' : '') . '">';
    $paginationHtml .= '<a class="page-link" href="?' . $queryStringNext . '">Siguiente</a></li>';
    $paginationHtml .= '</ul></nav>';
    return $paginationHtml;
}
?>

<div class="container mt-5 mb-5">
    <h2 class="text-center mb-4">Listado de Ventas</h2>

    <!-- Formulario de Búsqueda -->
    <form action="listado_ventas.php" method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-5">
                <input type="text" name="search_cedula" class="form-control" placeholder="Buscar por Cédula/NIT" value="<?= htmlspecialchars($searchCedulaNit ?? '') ?>">
            </div>
            <div class="col-md-5">
                <input type="text" name="search_nombre" class="form-control" placeholder="Buscar por Nombre Cliente" value="<?= htmlspecialchars($searchNombreCliente ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </div>
        </div>
    </form>

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

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Vendedor</th>
                    <th>Cliente</th>
                    <th>Cédula/NIT</th>
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
                            <td><?= htmlspecialchars($venta['nombreCliente']); ?></td>
                            <td><?= htmlspecialchars($venta['cedulaNit']); ?></td>
                            <td>$<?= htmlspecialchars(number_format($venta['totalVenta'], 2)); ?></td>
                            <td>
                                <a href="generar_factura.php?id_venta=<?= htmlspecialchars($venta['idVenta']); ?>" class="btn btn-info btn-sm" target="_blank" title="Ver Factura">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a href="delete_venta.php?id=<?= htmlspecialchars($venta['idVenta']); ?>" class="btn btn-danger btn-sm" title="Eliminar Venta" onclick="return confirm('¿Estás seguro de que quieres eliminar esta venta? Esta acción devolverá los productos al inventario.');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay ventas registradas que coincidan con la búsqueda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?= $paginationHtml; // Imprimir los enlaces de paginación ?>

</div>

<?php include("includes/footer.php"); ?>