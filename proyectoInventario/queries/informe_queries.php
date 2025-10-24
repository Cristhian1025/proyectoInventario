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
