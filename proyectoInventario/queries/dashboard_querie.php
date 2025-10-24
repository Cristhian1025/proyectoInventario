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
