<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/movimientosController.php';
require_once __DIR__ . '/../../controllers/ProductosController.php';

$db = new Database();
$conn = $db->connect();
$controller = new MovimientosController($conn);
$productosController = new ProductosController($conn);

$mensaje = '';
$tipo    = '';

// Procesar registro de entrada manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'entrada') {
    $id_producto = $_POST['id_producto'];
    $cantidad    = (int)$_POST['cantidad'];
    $motivo      = $_POST['motivo'] ?: 'Entrada de mercancía';

    if ($controller->registrarEntrada($id_producto, $cantidad, $motivo)) {
        $mensaje = "Entrada registrada correctamente.";
        $tipo    = 'success';
    } else {
        $mensaje = "Error al registrar la entrada.";
        $tipo    = 'danger';
    }
}

$filtro_producto = $_GET['producto'] ?? '';
$filtro_tipo      = $_GET['tipo'] ?? '';
$filtro_fecha     = $_GET['fecha'] ?? '';

$movimientos = $controller->listar($filtro_producto, $filtro_tipo, $filtro_fecha);
$productos   = $productosController->listar();

include __DIR__ . '/../../includes/layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Historial de Movimientos</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Trazabilidad completa del inventario — entradas, salidas y ajustes</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalEntrada">
        <i class="bi bi-box-arrow-in-down"></i> Registrar Entrada
    </button>
</div>

<?php if (!empty($mensaje)): ?>
<div class="alert alert-<?= $tipo ?> d-flex align-items-center gap-2 mb-4" style="border-radius:10px;">
    <i class="bi bi-<?= $tipo === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= $mensaje ?>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Producto</label>
                <input type="text" name="producto" class="form-control" placeholder="Buscar producto..."
                       value="<?= htmlspecialchars($filtro_producto) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de movimiento</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="entrada" <?= $filtro_tipo == 'entrada' ? 'selected' : '' ?>>Entrada</option>
                    <option value="salida" <?= $filtro_tipo == 'salida' ? 'selected' : '' ?>>Salida</option>
                    <option value="ajuste" <?= $filtro_tipo == 'ajuste' ? 'selected' : '' ?>>Ajuste</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($filtro_fecha) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de movimientos -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-2 text-primary"></i>Movimientos registrados</span>
        <span class="badge bg-primary"><?= count($movimientos) ?> registro(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th class="text-center">Cantidad</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($movimientos)): ?>
                    <?php foreach ($movimientos as $m): ?>
                    <tr>
                        <td style="font-size:0.825rem;color:#64748b;">
                            <?= date('d/m/Y H:i', strtotime($m['fecha_movimiento'])) ?>
                        </td>
                        <td>
                            <span style="font-weight:600;color:#1a2942;">
                                <?= htmlspecialchars($m['producto_nombre'] ?? 'Producto eliminado') ?>
                            </span>
                            <?php if($m['talla']): ?>
                            <span class="badge bg-light text-secondary border ms-1"><?= $m['talla'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $badges = [
                                'entrada' => ['bg-success', 'bi-box-arrow-in-down', 'Entrada'],
                                'salida'  => ['bg-danger', 'bi-box-arrow-up', 'Salida'],
                                'ajuste'  => ['bg-warning text-dark', 'bi-arrow-repeat', 'Ajuste'],
                            ];
                            $b = $badges[$m['tipo_movimiento']] ?? ['bg-secondary', 'bi-circle', $m['tipo_movimiento']];
                            ?>
                            <span class="badge <?= $b[0] ?>">
                                <i class="bi <?= $b[1] ?> me-1"></i><?= $b[2] ?>
                            </span>
                        </td>
                        <td class="text-center fw-bold" style="color:#1a2942;">
                            <?= $m['tipo_movimiento'] === 'salida' ? '-' : '+' ?><?= $m['cantidad'] ?>
                        </td>
                        <td style="font-size:0.825rem;color:#64748b;">
                            <?= htmlspecialchars($m['motivo'] ?? '—') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history d-block mb-2" style="font-size:2rem;"></i>
                            No hay movimientos registrados aún.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Registrar Entrada -->
<div class="modal fade" id="modalEntrada" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:#1a2942;">
                    <i class="bi bi-box-arrow-in-down me-2 text-success"></i>Registrar Entrada de Mercancía
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="entrada">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Producto <span class="text-danger">*</span></label>
                        <select name="id_producto" class="form-select" required>
                            <option value="">-- Seleccione un producto --</option>
                            <?php foreach ($productos as $p): ?>
                            <option value="<?= $p['id_producto'] ?>">
                                <?= htmlspecialchars($p['nombre']) ?> <?= $p['talla'] ? '('.$p['talla'].')' : '' ?> — Stock actual: <?= $p['cantidad'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad a ingresar <span class="text-danger">*</span></label>
                        <input type="number" name="cantidad" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo</label>
                        <input type="text" name="motivo" class="form-control" placeholder="Ej: Compra a proveedor, reposición...">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Registrar entrada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
