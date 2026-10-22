<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['admin']);
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/reportesController.php';

$db = new Database();
$conn = $db->connect();
$controller = new ReportesController($conn);

$resultado    = [];
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin    = $_POST['fecha_fin'] ?? null;
$total_general = 0;

if ($fecha_inicio && $fecha_fin) {
    $resultado = $controller->ventasPorRango($fecha_inicio, $fecha_fin);
    foreach ($resultado as $row) {
        $total_general += $row['total'];
    }

    // Exportar a Excel
    if (isset($_POST['exportar']) && $_POST['exportar'] === 'excel' && !empty($resultado)) {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Reporte_Ventas_" . date('Ymd') . ".xls");
        echo "<table border='1'>
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Productos</th>
                    <th>Total</th>
                </tr>";
        foreach ($resultado as $row) {
            echo "<tr>
                    <td>{$row['id_venta']}</td>
                    <td>{$row['fecha_venta']}</td>
                    <td>{$row['cliente']}</td>
                    <td>{$row['productos']}</td>
                    <td>{$row['total']}</td>
                  </tr>";
        }
        echo "<tr><td colspan='4'><strong>Total</strong></td><td><strong>$total_general</strong></td></tr>";
        echo "</table>";
        exit;
    }
}

include __DIR__ . '/../../includes/layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Reportes</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Consulta y exporta el historial de ventas por rango de fechas</p>
    </div>
</div>

<style>
/* Pestañas de Reportes: el estilo global .nav-link es para el menú lateral (texto blanco) */
.rep-tabs .nav-link { display:inline-flex; color:#475569; background:#fff; border:1px solid #e2e8f0; margin-right:6px; box-shadow:none; }
.rep-tabs .nav-link:hover { background:#eff6ff; color:#1d4ed8; }
.rep-tabs .nav-link.active { background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; border-color:#2563eb; box-shadow:0 4px 12px rgba(37,99,235,0.25); }
</style>
<ul class="nav nav-pills rep-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link active" href="reportes.php"><i class="bi bi-receipt me-1"></i> Ventas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="reporte_inventario.php"><i class="bi bi-box-seam me-1"></i> Inventario</a>
    </li>
</ul>

<!-- Filtro de fechas -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel me-2 text-primary"></i>Filtrar por rango de fechas
    </div>
    <div class="card-body p-4">
        <form method="POST">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Fecha inicio</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                        <input type="date" name="fecha_inicio" class="form-control"
                               value="<?= htmlspecialchars($fecha_inicio ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha fin</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                        <input type="date" name="fecha_fin" class="form-control"
                               value="<?= htmlspecialchars($fecha_fin ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search me-1"></i> Generar
                        </button>
                        <?php if (!empty($resultado)): ?>
                        <button type="submit" name="exportar" value="excel"
                                class="btn btn-success flex-grow-1">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resultados -->
<?php if (!empty($resultado)): ?>

<!-- Resumen -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-label">Total de ventas</div>
                <div class="stat-value"><?= count($resultado) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div>
                <div class="stat-label">Ingresos totales</div>
                <div class="stat-value">$<?= number_format($total_general, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card stat-orange">
            <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
            <div>
                <div class="stat-label">Promedio por venta</div>
                <div class="stat-value">$<?= number_format($total_general / count($resultado), 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de resultados -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span>
            <i class="bi bi-table me-2 text-primary"></i>
            Ventas del <?= date('d/m/Y', strtotime($fecha_inicio)) ?>
            al <?= date('d/m/Y', strtotime($fecha_fin)) ?>
        </span>
        <span class="badge bg-primary"><?= count($resultado) ?> registro(s)</span>
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
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resultado as $row): ?>
                <tr>
                    <td>
                        <span class="badge bg-light text-secondary border">#<?= $row['id_venta'] ?></span>
                    </td>
                    <td style="font-size:0.825rem;color:#64748b;">
                        <?= date('d/m/Y H:i', strtotime($row['fecha_venta'])) ?>
                    </td>
                    <td>
                        <?php if($row['cliente'] && $row['cliente'] !== 'Sin cliente'): ?>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                 style="width:28px;height:28px;font-size:0.7rem;font-weight:600;flex-shrink:0;">
                                <?= strtoupper(substr($row['cliente'], 0, 1)) ?>
                            </div>
                            <span style="font-size:0.875rem;"><?= htmlspecialchars($row['cliente']) ?></span>
                        </div>
                        <?php else: ?>
                        <span class="text-muted" style="font-size:0.825rem;">— Sin cliente</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.825rem;color:#475569;max-width:250px;">
                        <?= htmlspecialchars($row['productos']) ?>
                    </td>
                    <td class="text-end fw-bold" style="color:#1a2942;">
                        $<?= number_format($row['total'], 0, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc;">
                        <td colspan="4" class="text-end fw-bold" style="font-size:0.875rem;color:#64748b;">
                            Total general:
                        </td>
                        <td class="text-end fw-bold" style="font-size:1rem;color:#2563eb;">
                            $<?= number_format($total_general, 0, ',', '.') ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php elseif ($fecha_inicio && $fecha_fin): ?>
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-search d-block mb-2" style="font-size:2rem;"></i>
        No se encontraron ventas para el rango de fechas seleccionado.
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-graph-up d-block mb-2" style="font-size:2.5rem;color:#cbd5e1;"></i>
        <p class="mb-1" style="font-size:0.925rem;">Selecciona un rango de fechas para generar el reporte</p>
        <small>Los resultados mostrarán todas las ventas registradas en ese período</small>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
