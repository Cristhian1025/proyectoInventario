<?php
include("db.php");
require_once 'queries/venta_querie.php';
include("includes/header.php");

$ventas = getAllVentas($conn);
?>

<div class="container mt-5 mb-5">
    <h2 class="text-center mb-4">Listado de Ventas</h2>

    <?php if (isset($_SESSION['message'])) : ?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type']); ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    ?>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Vendedor</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ventas)) : ?>
                    <?php foreach ($ventas as $venta) : ?>
                        <tr>
                            <td><?= htmlspecialchars($venta['idVenta']); ?></td>
                            <td><?= htmlspecialchars(date("d/m/Y", strtotime($venta['fechaVenta']))); ?></td>
                            <td><?= htmlspecialchars($venta['vendedor']); ?></td>
                            <td>$<?= htmlspecialchars(number_format($venta['precioVentaTotal'], 2)); ?></td>
                            <td>
                                <a href="generar_factura.php?id_venta=<?= htmlspecialchars($venta['idVenta']); ?>" class="btn btn-info btn-sm" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Factura
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay ventas registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("includes/footer.php"); ?>