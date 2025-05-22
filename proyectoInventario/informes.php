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
    <h2>Informe de Ventas</h2>
    <form method="GET" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Desde:</label>
            <input type="date" name="start" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Hasta:</label>
            <input type="date" name="end" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Vendedor (opcional):</label>
            <select name="usuario" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['IdUsuario'] ?>"><?= htmlspecialchars($usuario['nombrecompleto']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Generar</button>
        </div>
    </form>

    <?php if ($reportData): ?>
        <table class="table table-striped table-bordered mt-4 mb-5">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Vendedor</th>
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
                        <td>$<?= number_format($row['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-secondary fw-bold">
                    <td colspan="2" class="text-end">Total General:</td>
                    <td>$<?= number_format($totalGeneral, 2) ?></td>
                </tr>
            </tbody>
        </table>
    <?php elseif ($_GET): ?>
        <p class="mt-4 alert alert-warning">No se encontraron resultados.</p>
    <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>
