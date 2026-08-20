<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ventasController.php';

$db = new Database();
$conn = $db->connect();
$controller = new VentasController($conn);

$filtro_cliente = $_GET['cliente'] ?? '';
$filtro_fecha    = $_GET['fecha'] ?? '';

$ventas = $controller->obtenerHistorialVentas($filtro_cliente, $filtro_fecha);

$total_general = 0;
foreach ($ventas as $v) { $total_general += $v['total']; }

// Ver detalle de una venta específica (AJAX simple vía GET)
$detalle_venta = null;
if (isset($_GET['ver_detalle'])) {
    $detalle_venta = $controller->obtenerDetalleVenta($_GET['ver_detalle']);
}

include __DIR__ . '/../../includes/layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Historial de Ventas</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Consulta todas las ventas registradas con filtros</p>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Cliente</label>
                <input type="text" name="cliente" class="form-control" placeholder="Buscar por nombre de cliente..."
                       value="<?= htmlspecialchars($filtro_cliente) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($filtro_fecha) ?>">
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <?php if ($filtro_cliente || $filtro_fecha): ?>
                    <a href="historial_ventas.php" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-label">Ventas encontradas</div>
                <div class="stat-value"><?= count($ventas) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div>
                <div class="stat-label">Total acumulado</div>
                <div class="stat-value">$<?= number_format($total_general, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-2 text-primary"></i>Ventas registradas</span>
        <span class="badge bg-primary"><?= count($ventas) ?> resultado(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#Venta</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Productos</th>
                        <th class="text-end">Total</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($ventas)): ?>
                    <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td><span class="badge bg-light text-secondary border">#<?= $v['id_venta'] ?></span></td>
                        <td style="font-size:0.825rem;color:#64748b;">
                            <?= date('d/m/Y H:i', strtotime($v['fecha_venta'])) ?>
                        </td>
                        <td style="font-size:0.875rem;">
                            <?= htmlspecialchars($v['cliente'] ?? '— Sin cliente') ?>
                        </td>
                        <td style="font-size:0.8rem;color:#64748b;max-width:280px;">
                            <?= htmlspecialchars($v['productos'] ?? '—') ?>
                        </td>
                        <td class="text-end fw-bold" style="color:#1a2942;">
                            $<?= number_format($v['total'], 0, ',', '.') ?>
                        </td>
                        <td>
                            <?php
                            $estados = [
                                'pagado'    => ['bg-success', 'Pagado'],
                                'abono'     => ['bg-warning text-dark', 'Abono'],
                                'pendiente' => ['bg-danger', 'Pendiente'],
                            ];
                            $e = $estados[$v['estado_pago'] ?? 'pagado'] ?? ['bg-secondary', $v['estado_pago']];
                            ?>
                            <span class="badge <?= $e[0] ?>"><?= $e[1] ?></span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="?ver_detalle=<?= $v['id_venta'] ?>&cliente=<?= urlencode($filtro_cliente) ?>&fecha=<?= $filtro_fecha ?>#detalle"
                                   class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="comprobante_venta.php?id=<?= $v['id_venta'] ?>" target="_blank"
                                   class="btn btn-sm btn-outline-success" title="Ver comprobante">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-search d-block mb-2" style="font-size:2rem;"></i>
                            No se encontraron ventas con esos filtros.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($detalle_venta): ?>
<div id="detalle" class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Detalle de la venta #<?= $_GET['ver_detalle'] ?></span>
        <a href="?cliente=<?= urlencode($filtro_cliente) ?>&fecha=<?= $filtro_fecha ?>" class="btn-close"></a>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Talla</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">Precio unitario</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($detalle_venta as $d): ?>
                <tr>
                    <td style="font-weight:600;color:#1a2942;"><?= htmlspecialchars($d['producto_nombre']) ?></td>
                    <td><?= $d['talla'] ? '<span class="badge bg-light text-secondary border">'.$d['talla'].'</span>' : '—' ?></td>
                    <td class="text-center"><?= $d['cantidad'] ?></td>
                    <td class="text-end">$<?= number_format($d['precio_unitario'], 0, ',', '.') ?></td>
                    <td class="text-end fw-bold">$<?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
