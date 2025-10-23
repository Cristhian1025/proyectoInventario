<?php

function getAllVentas($conn, int $page = 1, int $recordsPerPage = 10, ?string $searchCedulaNit = null, ?string $searchNombreCliente = null): array|false
{
    $offset = ($page - 1) * $recordsPerPage;
    $whereClauses = [];
    $params = [];
    $types = '';

    if ($searchCedulaNit !== null && $searchCedulaNit !== '') {
        $whereClauses[] = 'v.cedulaNit LIKE ?';
        $params[] = '%' . $searchCedulaNit . '%';
        $types .= 's';
    }

    if ($searchNombreCliente !== null && $searchNombreCliente !== '') {
        $whereClauses[] = 'v.nombreCliente LIKE ?';
        $params[] = '%' . $searchNombreCliente . '%';
        $types .= 's';
    }

    $whereSql = '';
    if (!empty($whereClauses)) {
        $whereSql = ' WHERE ' . implode(' AND ', $whereClauses);
    }

    // Contar el total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM ventas v" . $whereSql;
    $stmtCount = $conn->prepare($sqlCount);
    if ($stmtCount) {
        if (!empty($params)) {
            $stmtCount->bind_param($types, ...$params);
        }
        $stmtCount->execute();
        $resCount = $stmtCount->get_result();
        $totalRecords = $resCount->fetch_assoc()['total'] ?? 0;
        $stmtCount->close();
    } else {
        error_log("Error en getAllVentas (count): " . $conn->error);
        return false;
    }
    

    // Obtener los datos de la página actual
    $sql = "SELECT v.idVenta, v.fechaVenta, u.nombreCompleto as vendedor, v.totalVenta, v.cedulaNit, v.nombreCliente
              FROM ventas v
              INNER JOIN usuario u ON v.vendedorId = u.idUsuario" . $whereSql . "
              ORDER BY v.fechaVenta DESC, v.idVenta DESC
              LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error en getAllVentas (prepare): " . $conn->error);
        return false;
    }

    $allParams = $params;
    $allParams[] = $recordsPerPage;
    $allParams[] = $offset;
    $types .= 'ii';

    $stmt->bind_param($types, ...$allParams);
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

    // Obtener información principal de la venta
    $query_main = "SELECT v.idVenta, v.fechaVenta, u.nombreCompleto as vendedor, v.totalVenta, v.cedulaNit, v.nombreCliente
                   FROM ventas v
                   INNER JOIN usuario u ON v.vendedorId = u.idUsuario
                   WHERE v.idVenta = ?";
    
    $stmt_main = mysqli_prepare($conn, $query_main);
    mysqli_stmt_bind_param($stmt_main, "i", $idVenta);
    mysqli_stmt_execute($stmt_main);
    $result_main = mysqli_stmt_get_result($stmt_main);
    $details['venta'] = mysqli_fetch_assoc($result_main);

    if (!$details['venta']) {
        return null; // La venta no existe
    }

    // Obtener los productos de la venta (detalle)
    $query_items = "SELECT p.nombreProducto, dv.cantidad, dv.precioUnitario, (dv.cantidad * dv.precioUnitario) as importe
                    FROM detalle_venta dv
                    INNER JOIN productos p ON dv.productoId = p.idProducto
                    WHERE dv.ventaId = ?";

    $stmt_items = mysqli_prepare($conn, $query_items);
    mysqli_stmt_bind_param($stmt_items, "i", $idVenta);
    mysqli_stmt_execute($stmt_items);
    $result_items = mysqli_stmt_get_result($stmt_items);
    $details['items'] = mysqli_fetch_all($result_items, MYSQLI_ASSOC);
    
    // Renombrar 'cantidad' a 'cantidadVendida' para compatibilidad con la factura
    foreach ($details['items'] as &$item) {
        $item['cantidadVendida'] = $item['cantidad'];
        unset($item['cantidad']);
    }

    return $details;
}

?>