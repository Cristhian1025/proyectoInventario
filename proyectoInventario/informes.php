<?php 
    include("db.php");
    require_once 'queries/informe_queries.php'; 
    include("includes/header.php"); 

    $reportData = [];
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['start']) && isset($_GET['end'])) {
        $start = $_GET['start'];
        $end = $_GET['end'];
        $reportData = getSalesReportByDateRange($conn, $start, $end);
    }
?>

<div class="container mt-5 mb-5 pb-5">
    <h2 class="mb-4">Informe de Ventas</h2>
    
    <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-auto">
            <label for="start" class="form-label">Desde:</label>
            <input type="date" id="start" name="start" class="form-control" required>
        </div>
        <div class="col-auto">
            <label for="end" class="form-label">Hasta:</label>
            <input type="date" id="end" name="end" class="form-control" required>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Generar</button>
        </div>
    </form>

    <?php if ($reportData): ?>
        <div class="table-responsive mb-5">
            <table class="table table-bordered table-striped mb-5">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Total Ventas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['fecha']) ?></td>
                            <td>$<?= number_format($row['total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($_GET): ?>
        <div class="alert alert-warning" role="alert">
            No se encontraron resultados para el rango seleccionado.
        </div>
    <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>
