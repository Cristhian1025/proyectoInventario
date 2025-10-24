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
