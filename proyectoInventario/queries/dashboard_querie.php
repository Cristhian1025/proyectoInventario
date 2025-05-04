<?php
// queries/dashboard_querie.php

/**
 * Obtiene los productos más vendidos basados en la cantidad total vendida.
 *
 * @param mysqli $conn El objeto de conexión a la base de datos.
 * @param int $limit El número máximo de productos a retornar.
 * @return array|false Un array con los productos más vendidos o false en caso de error.
 */
function getTopSellingProducts(mysqli $conn, int $limit): array|false
{
    $limit = max(1, $limit); // Asegura que limit sea al menos 1

    $sql = "SELECT P.nombreProducto, SUM(V.cantidadVenta) AS total_vendido
            FROM ventas V
            LEFT JOIN Productos P ON V.productoId = P.idProducto
            WHERE P.nombreProducto IS NOT NULL  -- Evita agrupar ventas sin producto asociado
            GROUP BY P.idProducto, P.nombreProducto -- Agrupar también por nombre por si hay IDs duplicados (aunque no debería)
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
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $stmt->close();
    return $products;
}


/**
 * Obtiene los productos con la menor cantidad en stock.
 *
 * @param mysqli $conn El objeto de conexión a la base de datos.
 * @param int $limit El número máximo de productos a retornar.
 * @return array|false Un array con los productos o false en caso de error.
 */
function getLowStockProducts(mysqli $conn, int $limit): array|false
{
    $limit = max(1, $limit);

    $sql = "SELECT nombreProducto, cantidad
            FROM Productos
            ORDER BY cantidad ASC
            LIMIT ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparando consulta getLowStockProducts: " . $conn->error);
        return false;
    }

    $stmt->bind_param('i', $limit);

    if (!$stmt->execute()) {
        error_log("Error ejecutando consulta getLowStockProducts: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $products = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $stmt->close();
    return $products;
}

/**
 * Obtiene los datos de ventas totales diarias para los últimos N días (incluyendo hoy).
 *
 * @param mysqli $conn El objeto de conexión a la base de datos.
 * @param int $days El número de días hacia atrás a consultar (ej. 5 para hoy y los 4 días anteriores).
 * @return array Un array con dos claves: 'labels' (fechas Y-m-d) y 'data' (ventas totales por día).
 */
function getSalesDataForChart(mysqli $conn, int $days = 5): array
{
    $labels = [];
    $placeholders = [];
    $params = [];
    $types = '';

    // Generar fechas y placeholders para la consulta IN
    for ($i = ($days - 1); $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = $date;
        $placeholders[] = '?';
        $params[] = $date;
        $types .= 's'; // 's' para string (fecha)
    }

    $placeholders_string = implode(', ', $placeholders);

    $sql = "SELECT DATE(fechaVenta) AS fecha, SUM(precioVentaTotal) AS total_ventas
            FROM ventas
            WHERE DATE(fechaVenta) IN ($placeholders_string)
            GROUP BY DATE(fechaVenta)
            ORDER BY DATE(fechaVenta) ASC";

    $stmt = $conn->prepare($sql);
    $salesData = array_fill_keys($labels, 0); // Inicializar ventas en 0 para todas las fechas

    if ($stmt) {
        // Vincular los parámetros de fecha dinámicamente
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    // Llenar el array con los datos reales de la BD
                    if (isset($salesData[$row['fecha']])) {
                        $salesData[$row['fecha']] = $row['total_ventas'];
                    }
                }
            } else {
                 error_log("Error obteniendo resultados getSalesDataForChart: " . $stmt->error);
            }
        } else {
             error_log("Error ejecutando consulta getSalesDataForChart: " . $stmt->error);
        }
        $stmt->close();

    } else {
         error_log("Error preparando consulta getSalesDataForChart: " . $conn->error);
    }


    // Asegurar el orden correcto y retornar labels y data separados
    $orderedData = [];
    foreach ($labels as $label) {
        $orderedData[] = $salesData[$label];
    }

    return [
        'labels' => $labels,
        'data' => $orderedData
    ];
}


// --- FUNCIONES PARA MOSTRAR TABLAS ---
// (Idealmente, estas irían en sus propios archivos: product_queries.php, etc.)

/**
 * Obtiene productos filtrando por nombre, proveedor o categoría.
 */
function getFilteredProducts(mysqli $conn, string $filter = ''): array|false
{
    $searchTerm = "%" . $filter . "%";
    $sql = "SELECT P.idProducto, P.nombreProducto, P.descripcionProducto, P.cantidad,
                   P.precioVenta, P.precioCompra, Pr.nombreProveedor, C.nombreCategoria
            FROM Productos P
            LEFT JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor
            LEFT JOIN Categorias C ON P.CategoriaId = C.idCategoria
            WHERE P.nombreProducto LIKE ? OR Pr.nombreProveedor LIKE ? OR C.nombreCategoria LIKE ?";

    $stmt = $conn->prepare($sql);
     if (!$stmt) { error_log("Error prepare getFilteredProducts: " . $conn->error); return false; }
    $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
    if (!$stmt->execute()) { error_log("Error execute getFilteredProducts: " . $stmt->error); $stmt->close(); return false; }

    $result = $stmt->get_result();
    $data = [];
    if ($result) { while ($row = $result->fetch_assoc()) { $data[] = $row; } }
    $stmt->close();
    return $data;
}

/**
 * Obtiene todos los proveedores.
 */
function getAllProviders(mysqli $conn): array|false
{
    // Seleccionar solo columnas necesarias
    $sql = "SELECT idProveedor, nombreProveedor, descripcionProveedor, direccionProveedor, telefono, Correo, infoAdicional FROM Proveedores";
    $result = $conn->query($sql);
     if (!$result) { error_log("Error getAllProviders: " . $conn->error); return false; }
    $data = [];
    if ($result->num_rows > 0) { while ($row = $result->fetch_assoc()) { $data[] = $row; } }
    return $data;
}

/**
 * Obtiene productos con información básica de su proveedor.
 */
function getProductsWithProviders(mysqli $conn): array|false
{
    $sql = "SELECT P.nombreProducto, P.cantidad, Pr.nombreProveedor, Pr.telefono, Pr.Correo
            FROM Productos P
            INNER JOIN Proveedores Pr ON P.proveedorId = Pr.idProveedor";
    $result = $conn->query($sql);
     if (!$result) { error_log("Error getProductsWithProviders: " . $conn->error); return false; }
    $data = [];
    if ($result->num_rows > 0) { while ($row = $result->fetch_assoc()) { $data[] = $row; } }
    return $data;
}

/**
 * Obtiene todas las entradas de productos.
 */
function getAllEntries(mysqli $conn): array|false
{
    $sql = "SELECT E.idEntrada, E.fechaEntrada, P.nombreProducto, E.cantidadComprada, E.precioCompraUnidad, Pr.nombreProveedor
            FROM EntradaProductos E
            LEFT JOIN Productos P ON E.productoId = P.idProducto
            LEFT JOIN Proveedores Pr ON E.proveedorId = Pr.idProveedor
            ORDER BY E.idEntrada DESC"; // Ordenar por defecto puede ser útil
    $result = $conn->query($sql);
     if (!$result) { error_log("Error getAllEntries: " . $conn->error); return false; }
    $data = [];
    if ($result->num_rows > 0) { while ($row = $result->fetch_assoc()) { $data[] = $row; } }
    return $data;
}

/**
 * Obtiene las últimas N ventas.
 */
function getRecentSales(mysqli $conn, int $limit = 10): array|false
{
    $limit = max(1, $limit);
    $sql = "SELECT V.idVenta, V.fechaVenta, P.nombreProducto, V.cantidadVenta, V.precioVentaTotal, Vd.nombreCompleto
            FROM ventas V
            LEFT JOIN Productos P ON V.productoId = P.idProducto
            LEFT JOIN usuario Vd ON V.vendedorId = Vd.idUsuario
            ORDER BY V.idVenta DESC
            LIMIT ?";

    $stmt = $conn->prepare($sql);
     if (!$stmt) { error_log("Error prepare getRecentSales: " . $conn->error); return false; }
    $stmt->bind_param('i', $limit);
     if (!$stmt->execute()) { error_log("Error execute getRecentSales: " . $stmt->error); $stmt->close(); return false; }

    $result = $stmt->get_result();
    $data = [];
    if ($result) { while ($row = $result->fetch_assoc()) { $data[] = $row; } }
    $stmt->close();
    return $data;
}

?>