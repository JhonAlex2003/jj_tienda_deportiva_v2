<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['admin']);
require_once __DIR__ . '/../../config/db.php';

$db   = new Database();
$conn = $db->connect();

// ---- Filtros (GET) ----
$filtro_categoria = $_GET['categoria'] ?? '';
$filtro_estado    = $_GET['estado'] ?? 'todos';
if (!in_array($filtro_estado, ['todos', 'bajo', 'agotado', 'normal'], true)) {
    $filtro_estado = 'todos';
}

$categorias = $conn->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre ASC")
                   ->fetch_all(MYSQLI_ASSOC);

// ---- Consulta del inventario (solo productos activos) ----
// productos.categoria guarda el id de la categoría; se une con categorias para mostrar el nombre.
$sql = "SELECT p.id_producto, p.nombre, p.talla, p.cantidad, p.stock_minimo,
               p.costo_compra, p.precio,
               COALESCE(c.nombre, NULLIF(p.categoria, ''), 'Sin categoría') AS categoria_nombre
        FROM productos p
        LEFT JOIN categorias c ON c.id_categoria = p.categoria
        WHERE p.estado = 1";
$params = [];
$types  = '';

if ($filtro_categoria !== '' && ctype_digit($filtro_categoria)) {
    $sql .= " AND c.id_categoria = ?";
    $params[] = (int)$filtro_categoria;
    $types   .= 'i';
}
if ($filtro_estado === 'bajo') {
    $sql .= " AND p.cantidad <= p.stock_minimo";
} elseif ($filtro_estado === 'agotado') {
    $sql .= " AND p.cantidad = 0";
} elseif ($filtro_estado === 'normal') {
    $sql .= " AND p.cantidad > p.stock_minimo";
}
$sql .= " ORDER BY categoria_nombre ASC, p.nombre ASC, p.talla ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ---- Totales ----
$total_unidades   = 0;
$valor_costo      = 0;
$valor_venta      = 0;
$total_bajo       = 0;
$sin_costo        = 0;
foreach ($productos as $p) {
    $total_unidades += (int)$p['cantidad'];
    $valor_costo    += $p['cantidad'] * $p['costo_compra'];
    $valor_venta    += $p['cantidad'] * $p['precio'];
    if ($p['cantidad'] <= $p['stock_minimo']) { $total_bajo++; }
    if ($p['costo_compra'] <= 0)              { $sin_costo++; }
}

function estadoStock($cantidad, $minimo) {
    if ((int)$cantidad === 0)            { return 'Agotado'; }
    if ($cantidad <= $minimo)            { return 'Stock bajo'; }
    return 'Normal';
}

// ---- Exportar a Excel (antes de imprimir cualquier HTML) ----
if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel' && !empty($productos)) {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename=Reporte_Inventario_' . date('Ymd') . '.xls');
    echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>';
    echo '<table border="1">';
    echo '<tr><th>ID</th><th>Producto</th><th>Categoría</th><th>Talla</th><th>Cantidad</th>'
       . '<th>Stock mínimo</th><th>Estado</th><th>Costo de compra</th><th>Precio de venta</th>'
       . '<th>Valor a costo</th><th>Valor a precio de venta</th></tr>';
    foreach ($productos as $p) {
        echo '<tr>'
           . '<td>' . (int)$p['id_producto'] . '</td>'
           . '<td>' . htmlspecialchars($p['nombre']) . '</td>'
           . '<td>' . htmlspecialchars($p['categoria_nombre']) . '</td>'
           . '<td>' . htmlspecialchars((string)$p['talla']) . '</td>'
           . '<td>' . (int)$p['cantidad'] . '</td>'
           . '<td>' . (int)$p['stock_minimo'] . '</td>'
           . '<td>' . estadoStock($p['cantidad'], $p['stock_minimo']) . '</td>'
           . '<td>' . (float)$p['costo_compra'] . '</td>'
           . '<td>' . (float)$p['precio'] . '</td>'
           . '<td>' . ($p['cantidad'] * $p['costo_compra']) . '</td>'
           . '<td>' . ($p['cantidad'] * $p['precio']) . '</td>'
           . '</tr>';
    }
    echo '<tr><td colspan="4"><strong>Totales</strong></td><td><strong>' . $total_unidades . '</strong></td>'
       . '<td colspan="4"></td><td><strong>' . $valor_costo . '</strong></td>'
       . '<td><strong>' . $valor_venta . '</strong></td></tr>';
    echo '</table></body></html>';
    exit;
}

include __DIR__ . '/../../includes/layout.php';

$qsExport = http_build_query(['categoria' => $filtro_categoria, 'estado' => $filtro_estado, 'exportar' => 'excel']);
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Reportes</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Consulta y exporta el estado actual del inventario</p>
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
        <a class="nav-link" href="reportes.php"><i class="bi bi-receipt me-1"></i> Ventas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="reporte_inventario.php"><i class="bi bi-box-seam me-1"></i> Inventario</a>
    </li>
</ul>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel me-2 text-primary"></i>Filtrar inventario
    </div>
    <div class="card-body p-4">
        <form method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Categoría</label>
                    <select name="categoria" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>"
                            <?= (string)$cat['id_categoria'] === $filtro_categoria ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado del stock</label>
                    <select name="estado" class="form-select">
                        <option value="todos"   <?= $filtro_estado === 'todos'   ? 'selected' : '' ?>>Todos</option>
                        <option value="bajo"    <?= $filtro_estado === 'bajo'    ? 'selected' : '' ?>>Stock bajo (cantidad ≤ mínimo)</option>
                        <option value="agotado" <?= $filtro_estado === 'agotado' ? 'selected' : '' ?>>Agotados (cantidad 0)</option>
                        <option value="normal"  <?= $filtro_estado === 'normal'  ? 'selected' : '' ?>>Stock normal</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search me-1"></i> Generar
                        </button>
                        <?php if (!empty($productos)): ?>
                        <a href="reporte_inventario.php?<?= htmlspecialchars($qsExport) ?>"
                           class="btn btn-success flex-grow-1">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($productos)): ?>

<!-- Resumen -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="stat-label">Productos / unidades</div>
                <div class="stat-value"><?= count($productos) ?> / <?= number_format($total_unidades, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div>
                <div class="stat-label">Valor a costo</div>
                <div class="stat-value">$<?= number_format($valor_costo, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card stat-orange">
            <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
            <div>
                <div class="stat-label">Valor a precio de venta</div>
                <div class="stat-value">$<?= number_format($valor_venta, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card stat-red">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="stat-label">Con stock bajo</div>
                <div class="stat-value"><?= $total_bajo ?></div>
            </div>
        </div>
    </div>
</div>

<?php if ($sin_costo > 0): ?>
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4" style="border-radius:10px;font-size:0.85rem;">
    <i class="bi bi-info-circle"></i>
    <?= $sin_costo ?> producto(s) no tienen costo de compra registrado; su valor a costo aparece en $0.
    El costo se registra al hacer una entrada de inventario.
</div>
<?php endif; ?>

<!-- Tabla -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-table me-2 text-primary"></i>Inventario actual (<?= date('d/m/Y H:i') ?>)</span>
        <span class="badge bg-primary"><?= count($productos) ?> registro(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Talla</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">Mínimo</th>
                        <th>Estado</th>
                        <th class="text-end">Costo</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end">Valor a costo</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($productos as $p): ?>
                    <?php $estado = estadoStock($p['cantidad'], $p['stock_minimo']); ?>
                    <tr>
                        <td class="text-muted"><?= (int)$p['id_producto'] ?></td>
                        <td style="font-weight:600;color:#1a2942;"><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['categoria_nombre']) ?></td>
                        <td>
                            <?php if ($p['talla']): ?>
                            <span class="badge bg-light text-secondary border"><?= htmlspecialchars($p['talla']) ?></span>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td class="text-center fw-bold"><?= (int)$p['cantidad'] ?></td>
                        <td class="text-center text-muted"><?= (int)$p['stock_minimo'] ?></td>
                        <td>
                            <?php if ($estado === 'Agotado'): ?>
                                <span class="badge bg-danger">Agotado</span>
                            <?php elseif ($estado === 'Stock bajo'): ?>
                                <span class="badge bg-warning text-dark">Stock bajo</span>
                            <?php else: ?>
                                <span class="badge bg-success">Normal</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">$<?= number_format($p['costo_compra'], 0, ',', '.') ?></td>
                        <td class="text-end">$<?= number_format($p['precio'], 0, ',', '.') ?></td>
                        <td class="text-end fw-bold" style="color:#1a2942;">
                            $<?= number_format($p['cantidad'] * $p['costo_compra'], 0, ',', '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc;">
                        <td colspan="4" class="text-end fw-bold" style="font-size:0.875rem;color:#64748b;">Totales:</td>
                        <td class="text-center fw-bold"><?= number_format($total_unidades, 0, ',', '.') ?></td>
                        <td colspan="4"></td>
                        <td class="text-end fw-bold" style="color:#2563eb;">$<?= number_format($valor_costo, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-search d-block mb-2" style="font-size:2rem;"></i>
        No hay productos que coincidan con el filtro seleccionado.
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
