<?php 
include("db.php");
require_once 'queries/informe_queries.php'; 
include("includes/header.php"); 

$reportData = [];
$usuarios = getAllUsuarios($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['start']) && isset($_GET['end'])) {
    $start = $_GET['start'];
    $end = $_GET['end'];
    $vendedorId = isset($_GET['usuario']) && $_GET['usuario'] !== '' ? $_GET['usuario'] : null;
    $reportData = getSalesReportByDateRange($conn, $start, $end, $vendedorId);
}
?>

<div class="container mt-4 mb-5 pb-5">
    <div class="card card-glass">
        <div class="card-header">
            <h2 class="mb-0">Informe de Ventas</h2>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Desde:</label>
                    <input type="date" name="start" class="form-control" required value="<?= htmlspecialchars($_GET['start'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta:</label>
                    <input type="date" name="end" class="form-control" required value="<?= htmlspecialchars($_GET['end'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vendedor (opcional):</label>
                    <select name="usuario" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= $usuario['IdUsuario'] ?>" <?= (isset($_GET['usuario']) && $_GET['usuario'] == $usuario['IdUsuario']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($usuario['nombrecompleto']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">Generar</button>
                        <button type="submit" formtarget="_blank" formaction="exportar_informe.php" class="btn btn-success">Exportar PDF</button>
                    </div>
                </div>
            </form>

            <?php if ($reportData): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover mt-4 mb-5">
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
                            $totalGeneral = 0;
                            foreach ($reportData as $row): 
                                $totalGeneral += $row['total'];
                            ?>
                                <tr>
                                    <td><?= $row['fechaVenta'] ?></td>
                                    <td><?= htmlspecialchars($row['nombrecompleto']) ?></td>
                                    <td><?= htmlspecialchars($row['numero_ventas']) ?></td>
                                    <td>$<?= number_format($row['total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-secondary fw-bold">
                                <td colspan="3" class="text-end">Total General:</td>
                                <td>$<?= number_format($totalGeneral, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php elseif (isset($_GET['start'])): ?>
                <p class="mt-4 alert alert-warning">No se encontraron resultados para los filtros seleccionados.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>
