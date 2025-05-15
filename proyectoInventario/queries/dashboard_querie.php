<?php
// queries/dashboard_querie.php

// ... (funciones getTopSellingProducts, getLowStockProducts, getSalesDataForChart permanecen igual por ahora,
// ya que no se suelen paginar estos resúmenes) ...

/**
 * Obtiene productos filtrando por nombre, proveedor o categoría, con paginación.
 *
 * @param mysqli $conn
 * @param string $filter
 * @param int $page
 * @param int $recordsPerPage
 * @return array|false ['data' => array, 'totalRecords' => int] o false en error
 */
function getFilteredProducts(mysqli $conn, string $filter = '', int $page = 1, int $recordsPerPage = 10): array|false
{
    $searchTerm = "%" . $filter . "%";
    $offset = ($page - 1) * $recordsPerPage;

    // Consulta para el CONTEO TOTAL de registros que coinciden con el filtro
    $sqlCount = "SELECT COUNT(P.idProducto) as total
                 FROM Productos P
                 LEFT JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor
                 LEFT JOIN Categorias C ON P.CategoriaId = C.idCategoria
                 WHERE P.nombreProducto LIKE ? OR Pr.nombreProveedor LIKE ? OR C.nombreCategoria LIKE ?";

    $stmtCount = $conn->prepare($sqlCount);
    if (!$stmtCount) {
        error_log("Error prepare count getFilteredProducts: " . $conn->error);
        return false;
    }
    $stmtCount->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
    if (!$stmtCount->execute()) {
        error_log("Error execute count getFilteredProducts: " . $stmtCount->error);
        $stmtCount->close();
        return false;
    }
    $resultCount = $stmtCount->get_result();
    $totalRecords = $resultCount->fetch_assoc()['total'] ?? 0;
    $stmtCount->close();

    // Consulta para OBTENER LOS DATOS de la página actual
    $sqlData = "SELECT P.idProducto, P.nombreProducto, P.descripcionProducto, P.cantidad,
                       P.precioVenta, P.precioCompra, Pr.nombreProveedor, C.nombreCategoria
                FROM Productos P
                LEFT JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor
                LEFT JOIN Categorias C ON P.CategoriaId = C.idCategoria
                WHERE P.nombreProducto LIKE ? OR Pr.nombreProveedor LIKE ? OR C.nombreCategoria LIKE ?
                ORDER BY P.idProducto DESC -- O el orden que prefieras
                LIMIT ? OFFSET ?";

    $stmtData = $conn->prepare($sqlData);
    if (!$stmtData) {
        error_log("Error prepare data getFilteredProducts: " . $conn->error);
        return false;
    }
    // 'sssii' -> 3 strings para LIKE, 2 integers para LIMIT y OFFSET
    $stmtData->bind_param('sssii', $searchTerm, $searchTerm, $searchTerm, $recordsPerPage, $offset);
    if (!$stmtData->execute()) {
        error_log("Error execute data getFilteredProducts: " . $stmtData->error);
        $stmtData->close();
        return false;
    }

    $resultData = $stmtData->get_result();
    $data = [];
    if ($resultData) {
        while ($row = $resultData->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmtData->close();

    return ['data' => $data, 'totalRecords' => (int)$totalRecords];
}


/**
 * Obtiene todos los proveedores, con paginación.
 *
 * @param mysqli $conn
 * @param int $page
 * @param int $recordsPerPage
 * @return array|false ['data' => array, 'totalRecords' => int] o false en error
 */
function getAllProviders(mysqli $conn, int $page = 1, int $recordsPerPage = 10): array|false
{
    $offset = ($page - 1) * $recordsPerPage;

    // CONTEO TOTAL
    $sqlCount = "SELECT COUNT(*) as total FROM Proveedores";
    $resultCount = $conn->query($sqlCount);
    if (!$resultCount) {
        error_log("Error count getAllProviders: " . $conn->error);
        return false;
    }
    $totalRecords = $resultCount->fetch_assoc()['total'] ?? 0;

    // DATOS DE LA PÁGINA
    $sqlData = "SELECT idProveedor, nombreProveedor, descripcionProveedor, direccionProveedor, telefono, Correo, infoAdicional
                FROM Proveedores
                ORDER BY idProveedor DESC -- O el orden que prefieras
                LIMIT ? OFFSET ?";
    $stmtData = $conn->prepare($sqlData);
    if (!$stmtData) {
        error_log("Error prepare data getAllProviders: " . $conn->error);
        return false;
    }
    $stmtData->bind_param('ii', $recordsPerPage, $offset);
    if (!$stmtData->execute()) {
        error_log("Error execute data getAllProviders: " . $stmtData->error);
        $stmtData->close();
        return false;
    }
    $resultData = $stmtData->get_result();
    $data = [];
    if ($resultData) {
        while ($row = $resultData->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmtData->close();
    return ['data' => $data, 'totalRecords' => (int)$totalRecords];
}

// ... (modifica getProductsWithProviders, getAllEntries, getRecentSales de forma similar) ...
// Por ejemplo, para getRecentSales, ya tenías un LIMIT, ahora harías que ese LIMIT sea dinámico
// y también calcularías el total de ventas (que podría no tener sentido paginar si siempre son las "últimas 10"
// a menos que quieras paginar sobre *todas* las ventas).
// Por simplicidad, me enfocaré en paginar 'productos' y 'proveedores' en dashboard.php

// --- FUNCIONES DASHBOARD widgets (getTopSellingProducts, getLowStockProducts, getSalesDataForChart) ---
// Estas funciones generalmente no se paginan ya que muestran un resumen.
// Si una de estas listas crece mucho, podrías considerar un "ver más" que lleve a una página paginada.
function getTopSellingProducts(mysqli $conn, int $limit = 7): array|false
{
    $limit = max(1, $limit);

    $sql = "SELECT P.nombreProducto, SUM(V.cantidadVenta) AS total_vendido
            FROM ventas V
            LEFT JOIN Productos P ON V.productoId = P.idProducto
            WHERE P.nombreProducto IS NOT NULL
            GROUP BY P.idProducto, P.nombreProducto
            ORDER BY total_vendido DESC
            LIMIT ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparando consulta getTopSellingProducts: " . $conn->error);
        return false;
    }
    $stmt->bind_param('i', $limit);
    if (!$stmt->execute()) {
        error_log("Error ejecutando consulta getTopSellingProducts: " . $stmt->error);
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    $products = [];
    if ($result) { while ($row = $result->fetch_assoc()) { $products[] = $row; } }
    $stmt->close();
    return $products;
}

function getLowStockProducts(mysqli $conn, int $limit = 7): array|false
{
    $limit = max(1, $limit);
    $sql = "SELECT nombreProducto, cantidad FROM Productos ORDER BY cantidad ASC LIMIT ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { error_log("Error preparando consulta getLowStockProducts: " . $conn->error); return false; }
    $stmt->bind_param('i', $limit);
    if (!$stmt->execute()) { error_log("Error ejecutando consulta getLowStockProducts: " . $stmt->error); $stmt->close(); return false; }
    $result = $stmt->get_result();
    $products = [];
    if ($result) { while ($row = $result->fetch_assoc()) { $products[] = $row; } }
    $stmt->close();
    return $products;
}

function getSalesDataForChart(mysqli $conn, int $days): array
{
    // ... (código de getSalesDataForChart sin cambios, ya que no se pagina) ...
    $labels = [];
    $placeholders = [];
    $params = [];
    $types = '';
    for ($i = ($days - 1); $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = $date;
        $placeholders[] = '?';
        $params[] = $date;
        $types .= 's';
    }
    $placeholders_string = implode(', ', $placeholders);
    $sql = "SELECT DATE(fechaVenta) AS fecha, SUM(precioVentaTotal) AS total_ventas
            FROM ventas
            WHERE DATE(fechaVenta) IN ($placeholders_string)
            GROUP BY DATE(fechaVenta)
            ORDER BY DATE(fechaVenta) ASC";
    $stmt = $conn->prepare($sql);
    $salesData = array_fill_keys($labels, 0);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($salesData[$row['fecha']])) {
                        $salesData[$row['fecha']] = $row['total_ventas'];
                    }
                }
            } else { error_log("Error obteniendo resultados getSalesDataForChart: " . $stmt->error); }
        } else { error_log("Error ejecutando consulta getSalesDataForChart: " . $stmt->error); }
        $stmt->close();
    } else { error_log("Error preparando consulta getSalesDataForChart: " . $conn->error); }
    $orderedData = [];
    foreach ($labels as $label) { $orderedData[] = $salesData[$label]; }
    return ['labels' => $labels, 'data' => $orderedData ];
}

// Adapta las siguientes funciones para que también retornen ['data' => $data, 'totalRecords' => $totalRecords]
// y acepten $page y $recordsPerPage. Por brevedad, no las modifico aquí completamente.

function getProductsWithProviders(mysqli $conn, int $page = 1, int $recordsPerPage = 10): array|false
{
    $offset = ($page - 1) * $recordsPerPage;
    $sqlCount = "SELECT COUNT(P.idProducto) as total FROM Productos P INNER JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor";
    $resCount = $conn->query($sqlCount);
    $totalRecords = $resCount ? $resCount->fetch_assoc()['total'] : 0;

    $sql = "SELECT P.nombreProducto, P.cantidad, Pr.nombreProveedor, Pr.telefono, Pr.Correo
            FROM Productos P
            INNER JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor
            ORDER BY P.idProducto DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if(!$stmt) return false;
    $stmt->bind_param('ii', $recordsPerPage, $offset);
    if(!$stmt->execute()) return false;
    $result = $stmt->get_result();
    $data = [];
    if ($result) { while ($row = $result->fetch_assoc()) { $data[] = $row; } }
    $stmt->close();
    return ['data' => $data, 'totalRecords' => (int)$totalRecords];
}

function getAllEntries(mysqli $conn, int $page = 1, int $recordsPerPage = 10): array|false
{
    $offset = ($page - 1) * $recordsPerPage;
    $sqlCount = "SELECT COUNT(*) as total FROM EntradaProductos";
    $resCount = $conn->query($sqlCount);
    $totalRecords = $resCount ? $resCount->fetch_assoc()['total'] : 0;

    $sql = "SELECT E.idEntrada, E.fechaEntrada, P.nombreProducto, E.cantidadComprada, E.precioCompraUnidad, Pr.nombreProveedor
            FROM EntradaProductos E
            LEFT JOIN Productos P ON E.productoId = P.idProducto
            LEFT JOIN Proveedores Pr ON E.proveedorId = Pr.idProveedor
            ORDER BY E.idEntrada DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if(!$stmt) return false;
    $stmt->bind_param('ii', $recordsPerPage, $offset);
    if(!$stmt->execute()) return false;
    $result = $stmt->get_result();
    $data = [];
    if ($result) { while ($row = $result->fetch_assoc()) { $data[] = $row; } }
    $stmt->close();
    return ['data' => $data, 'totalRecords' => (int)$totalRecords];
}

function getRecentSales(mysqli $conn, int $page = 1, int $recordsPerPage = 10): array|false
{
    // Nota: "Recent sales" usualmente es solo un LIMIT. Si quieres paginar TODAS las ventas:
    $offset = ($page - 1) * $recordsPerPage;
    $sqlCount = "SELECT COUNT(*) as total FROM ventas"; // Contar todas las ventas
    $resCount = $conn->query($sqlCount);
    $totalRecords = $resCount ? $resCount->fetch_assoc()['total'] : 0;

    $sql = "SELECT V.idVenta, V.fechaVenta, P.nombreProducto, V.cantidadVenta, V.precioVentaTotal, Vd.nombreCompleto
            FROM ventas V
            LEFT JOIN Productos P ON V.productoId = P.idProducto
            LEFT JOIN usuario Vd ON V.vendedorId = Vd.idUsuario
            ORDER BY V.idVenta DESC
            LIMIT ? OFFSET ?"; // Paginando sobre todas las ventas

    $stmt = $conn->prepare($sql);
    if (!$stmt) { error_log("Error prepare getRecentSales: " . $conn->error); return false; }
    $stmt->bind_param('ii', $recordsPerPage, $offset); // 'ii' para $recordsPerPage y $offset
    if (!$stmt->execute()) { error_log("Error execute getRecentSales: " . $stmt->error); $stmt->close(); return false; }

    $result = $stmt->get_result();
    $data = [];
    if ($result) { while ($row = $result->fetch_assoc()) { $data[] = $row; } }
    $stmt->close();
    return ['data' => $data, 'totalRecords' => (int)$totalRecords];
}

?>