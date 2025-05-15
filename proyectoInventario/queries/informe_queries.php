<?php
function getSalesReportByDateRange(mysqli $conn, string $startDate, string $endDate): array|false {
    $sql = "SELECT DATE(fechaVenta) AS fecha, SUM(precioVentaTotal) AS total
            FROM ventas
            WHERE DATE(fechaVenta) BETWEEN ? AND ?
            GROUP BY DATE(fechaVenta)
            ORDER BY fecha ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparando informe: " . $conn->error);
        return false;
    }

    $stmt->bind_param('ss', $startDate, $endDate);
    if (!$stmt->execute()) {
        error_log("Error ejecutando informe: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $report = [];
    while ($row = $result->fetch_assoc()) {
        $report[] = $row;
    }

    $stmt->close();
    return $report;
}
