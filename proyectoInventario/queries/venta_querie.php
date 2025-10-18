<?php

function getAllVentas($conn) {
    $query = "SELECT v.idVenta, v.fechaVenta, u.nombreCompleto as vendedor, v.precioVentaTotal
              FROM ventas v
              INNER JOIN usuario u ON v.vendedorId = u.idUsuario
              ORDER BY v.fechaVenta DESC, v.idVenta DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("Error en getAllVentas: " . mysqli_error($conn));
        return [];
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getVentaDetailsById($conn, $idVenta) {
    $details = [];

    // Obtener todas las filas de la venta
    $query = "SELECT v.idVenta, v.fechaVenta, u.nombreCompleto as vendedor, v.precioVentaTotal, p.nombreProducto, v.cantidadVenta, (v.precioVentaTotal / v.cantidadVenta) as precioUnitario
              FROM ventas v
              INNER JOIN usuario u ON v.vendedorId = u.idUsuario
              INNER JOIN productos p ON v.productoId = p.idProducto
              WHERE v.idVenta = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idVenta);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

    if (empty($rows)) {
        return null; // La venta no existe
    }

    // La información principal es la misma para todas las filas de esta venta
    $details['venta'] = [
        'idVenta' => $rows[0]['idVenta'],
        'fechaVenta' => $rows[0]['fechaVenta'],
        'vendedor' => $rows[0]['vendedor'],
        // Sumar el total de todos los productos de la venta
        'precioVentaTotal' => array_sum(array_column($rows, 'precioVentaTotal'))
    ];

    // Mapear los items
    $details['items'] = array_map(function($row) {
        return [
            'nombreProducto' => $row['nombreProducto'],
            'cantidadVendida' => $row['cantidadVenta'],
            'precioUnitario' => $row['precioUnitario'],
            'importe' => $row['precioVentaTotal']
        ];
    }, $rows);

    return $details;
}

?>