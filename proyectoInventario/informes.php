<?php
/**
 * informes.php
 *
 * Página para la generación y visualización de informes de ventas.
 * Permite al usuario consultar el total de ventas por fecha y por vendedor,
 * así como exportar los resultados en formato PDF.
 *
 * @author  
 * @version 1.0
 * @date    2025-10-20
 */

include("db.php"); // Conexión a la base de datos.
require_once 'queries/informe_queries.php'; // Funciones SQL específicas para los informes.
include("includes/header.php"); // Encabezado HTML común del sitio (navegación, estilos, etc.).

// Inicializa variables
$reportData = []; // Almacena los datos del informe generados
$usuarios = getAllUsuarios($conn); // Obtiene la lista de vendedores registrados

// Verifica si el formulario fue enviado mediante método GET con los parámetros requeridos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['start']) && isset($_GET['end'])) {
    $start = $_GET['start']; // Fecha de inicio del rango
    $end = $_GET['end'];     // Fecha final del rango

    // Si se seleccionó un vendedor específico, se filtra por su ID
    $vendedorId = isset($_GET['usuario']) && $_GET['usuario'] !== '' ? $_GET['usuario'] : null;

    // Consulta los datos del informe usando el rango de fechas y, si aplica, el vendedor
    $reportData = getSalesReportByDateRange($conn, $start, $end, $vendedorId);
}
?>

<!-- Contenedor principal de la página de informes -->
<div class="container mt-4 mb-5 pb-5">
    <h2>Informe de Ventas</h2>

    <!-- Formulario de filtrado de informes -->
    <form method="GET" class="row g-3">
        <!-- Campo de fecha de inicio -->
        <div class="col-md-3">
            <label class="form-label">Desde:</label>
            <input type="date" name="start" class="form-control" required>
        </div>

        <!-- Campo de fecha de finalización -->
        <div class="col-md-3">
            <label class="form-label">Hasta:</label>
            <input type="date" name="end" class="form-control" required>
        </div>

        <!-- Selección opcional de vendedor -->
        <div class="col-md-3">
            <label class="form-label">Vendedor (opcional):</label>
            <select name="usuario" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['IdUsuario'] ?>">
                        <?= htmlspecialchars($usuario['nombrecompleto']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Botón para generar informe -->
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Generar</button>
        </div>

        <!-- Botón para exportar informe a PDF (abre en nueva pestaña) -->
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" formtarget="_blank" formaction="exportar_informe.php" class="btn btn-success w-100">
                Exportar PDF
            </button>
        </div>
    </form>

    <!-- Si existen datos, se muestran en una tabla -->
    <?php if ($reportData): ?>
        <table class="table table-striped table-bordered mt-4 mb-5">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Vendedor</th>
                    <th>Nro. Ventas</th>
                    <th>Total Vendido</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalGeneral = 0; // Acumulador para el total general de ventas

                // Recorre los resultados del informe
                foreach ($reportData as $row): 
                    $totalGeneral += $row['total']; // Suma el total de cada día o vendedor
                ?>
                    <tr>
                        <td><?= $row['fechaVenta'] ?></td>
                        <td><?= htmlspecialchars($row['nombrecompleto']) ?></td>
                        <td><?= htmlspecialchars($row['numero_ventas']) ?></td>
                        <td>$<?= number_format($row['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Fila con el total general -->
                <tr class="table-secondary fw-bold">
                    <td colspan="3" class="text-end">Total General:</td>
                    <td>$<?= number_format($totalGeneral, 2) ?></td>
                </tr>
            </tbody>
        </table>

    <!-- Si se hizo una búsqueda pero no se encontraron resultados -->
    <?php elseif ($_GET): ?>
        <p class="mt-4 alert alert-warning">No se encontraron resultados.</p>
    <?php endif; ?>
</div>

<!-- Pie de página del sitio -->
<?php include("includes/footer.php"); ?>
