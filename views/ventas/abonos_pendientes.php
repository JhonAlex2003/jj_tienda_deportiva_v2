<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/abonosController.php';

$db = new Database();
$conn = $db->connect();
$controller = new AbonosController($conn);

$mensaje = '';
$tipo    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'abonar') {
    $id_venta = $_POST['id_venta'];
    $monto    = (float)$_POST['monto'];
    $nota     = $_POST['nota'] ?: null;

    if ($controller->registrarAbono($id_venta, $monto, $nota)) {
        $mensaje = "Abono registrado correctamente.";
        $tipo    = 'success';
    } else {
        $mensaje = "Error al registrar el abono.";
        $tipo    = 'danger';
    }
}

$pendientes = $controller->listarPendientes();
$resumen    = $controller->resumen();

include __DIR__ . '/../../includes/layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Abonos Pendientes</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Control de pagos parciales y saldos por cobrar</p>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
<div class="alert alert-<?= $tipo ?> d-flex align-items-center gap-2 mb-4" style="border-radius:10px;">
    <i class="bi bi-<?= $tipo === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= $mensaje ?>
</div>
<?php endif; ?>

<!-- Resumen -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="stat-card stat-red">
            <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="stat-label">Total por cobrar</div>
                <div class="stat-value">$<?= number_format($resumen['total_adeudado'], 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card stat-orange">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-label">Ventas con saldo</div>
                <div class="stat-value"><?= $resumen['total_ventas_pendientes'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-label">Clientes con deuda</div>
                <div class="stat-value"><?= $resumen['total_clientes'] ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-exclamation-circle me-2 text-danger"></i>Saldos pendientes por venta
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#Venta</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Abonado</th>
                        <th class="text-end">Saldo</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($pendientes)): ?>
                    <?php foreach ($pendientes as $v): ?>
                    <tr>
                        <td><span class="badge bg-light text-secondary border">#<?= $v['id_venta'] ?></span></td>
                        <td style="font-size:0.825rem;color:#64748b;">
                            <?= date('d/m/Y', strtotime($v['fecha_venta'])) ?>
                        </td>
                        <td>
                            <?php if($v['cliente']): ?>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                     style="width:28px;height:28px;font-size:0.7rem;font-weight:600;flex-shrink:0;">
                                    <?= strtoupper(substr($v['cliente'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-size:0.875rem;font-weight:500;"><?= htmlspecialchars($v['cliente']) ?></div>
                                    <?php if($v['telefono']): ?>
                                    <small class="text-muted"><?= htmlspecialchars($v['telefono']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <span class="text-muted">— Sin cliente</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end" style="font-size:0.875rem;">
                            $<?= number_format($v['total'], 0, ',', '.') ?>
                        </td>
                        <td class="text-end text-success" style="font-size:0.875rem;">
                            $<?= number_format($v['total_abonado'], 0, ',', '.') ?>
                        </td>
                        <td class="text-end fw-bold text-danger">
                            $<?= number_format($v['saldo_pendiente'], 0, ',', '.') ?>
                        </td>
                        <td>
                            <?php if($v['estado_pago'] === 'abono'): ?>
                            <span class="badge bg-warning text-dark">Abono</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal" data-bs-target="#modalAbono"
                                    data-venta="<?= $v['id_venta'] ?>"
                                    data-saldo="<?= $v['saldo_pendiente'] ?>"
                                    data-cliente="<?= htmlspecialchars($v['cliente'] ?? 'Sin cliente') ?>">
                                <i class="bi bi-cash"></i> Abonar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle d-block mb-2" style="font-size:2rem;color:#10b981;"></i>
                            No hay saldos pendientes. ¡Todo al día!
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal registrar abono -->
<div class="modal fade" id="modalAbono" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:#1a2942;">
                    <i class="bi bi-cash-coin me-2 text-primary"></i>Registrar Abono
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="abonar">
                <input type="hidden" name="id_venta" id="modal_id_venta">
                <div class="modal-body">
                    <div class="alert alert-info d-flex justify-content-between align-items-center" style="border-radius:10px;">
                        <span id="modal_cliente" style="font-size:0.875rem;"></span>
                        <span>Saldo: <strong id="modal_saldo"></strong></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto a abonar <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="monto" id="modal_monto" class="form-control" min="1" step="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nota (opcional)</label>
                        <input type="text" name="nota" class="form-control" placeholder="Ej: Pago en efectivo">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Registrar abono
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('modalAbono').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('modal_id_venta').value = btn.dataset.venta;
    document.getElementById('modal_cliente').textContent = btn.dataset.cliente;
    document.getElementById('modal_saldo').textContent = '$' + Number(btn.dataset.saldo).toLocaleString('es-CO');
    document.getElementById('modal_monto').max = btn.dataset.saldo;
    document.getElementById('modal_monto').value = btn.dataset.saldo;
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
