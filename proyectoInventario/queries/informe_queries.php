<?php
function getSalesReportByDateRange($conn, $startDate, $endDate, $vendedorId = null) {
    if ($vendedorId) {
        $query = "SELECT v.fechaVenta, u.nombrecompleto, COUNT(DISTINCT v.idVenta) as numero_ventas, SUM(v.totalVenta) as total 
                  FROM ventas v
                  INNER JOIN Usuario u ON v.vendedorId = u.IdUsuario
                  WHERE v.fechaVenta BETWEEN ? AND ? AND v.vendedorId = ?
                  GROUP BY v.fechaVenta, u.nombrecompleto
                  ORDER BY v.fechaVenta ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $startDate, $endDate, $vendedorId);
    } else {
        $query = "SELECT v.fechaVenta, u.nombrecompleto, COUNT(DISTINCT v.idVenta) as numero_ventas, SUM(v.totalVenta) as total 
                  FROM ventas v
                  INNER JOIN Usuario u ON v.vendedorId = u.IdUsuario
                  WHERE v.fechaVenta BETWEEN ? AND ?
                  GROUP BY v.fechaVenta, u.nombrecompleto
                  ORDER BY v.fechaVenta ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $startDate, $endDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

function getAllUsuarios($conn) {
    $result = $conn->query("SELECT IdUsuario, nombrecompleto FROM Usuario");
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
