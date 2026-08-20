<?php
include "../includes/layout.php";
require_once "../config/db.php";

$db = new Database();
$conn = $db->connect();

/* ===============================
   ESTADÍSTICAS
   =============================== */
$result = $conn->query("SELECT COUNT(*) as total FROM productos WHERE estado = 1");
$total_productos = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM productos WHERE cantidad <= stock_minimo AND estado = 1");
$productos_bajos = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM clientes");
$total_clientes = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COALESCE(SUM(total),0) as total FROM ventas WHERE DATE(fecha_venta) = CURDATE()");
$ventas_hoy = $result->fetch_assoc()['total'];

/* ===============================
   GRÁFICAS
   =============================== */
$resSemana = $conn->query("
    SELECT DAYNAME(fecha_venta) AS dia, SUM(total) AS total
    FROM ventas
    WHERE YEARWEEK(fecha_venta, 1) = YEARWEEK(CURDATE(), 1)
    GROUP BY DAYNAME(fecha_venta)
");
$diasSemana = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];
$datosSemana = array_fill(0, 7, 0);
while ($fila = $resSemana->fetch_assoc()) {
    $index = array_search($fila["dia"], $diasSemana);
    if ($index !== false) $datosSemana[$index] = (float)$fila["total"];
}

$resMes = $conn->query("
    SELECT DATE(fecha_venta) AS fecha, SUM(total) AS total
    FROM ventas
    WHERE MONTH(fecha_venta) = MONTH(CURDATE()) AND YEAR(fecha_venta) = YEAR(CURDATE())
    GROUP BY DATE(fecha_venta)
");
$labelsMes = []; $datosMes = [];
while ($fila = $resMes->fetch_assoc()) {
    $labelsMes[] = date('d M', strtotime($fila["fecha"]));
    $datosMes[] = (float)$fila["total"];
}

$resGanancias = $conn->query("
    SELECT WEEK(fecha_venta, 1) AS semana, SUM(total) AS total
    FROM ventas
    WHERE MONTH(fecha_venta) = MONTH(CURDATE()) AND YEAR(fecha_venta) = YEAR(CURDATE())
    GROUP BY WEEK(fecha_venta, 1)
");
$labelsGanancias = []; $datosGanancias = [];
while ($fila = $resGanancias->fetch_assoc()) {
    $labelsGanancias[] = "Semana " . $fila["semana"];
    $datosGanancias[] = (float)$fila["total"];
}

/* ===============================
   PRODUCTOS CON BAJO STOCK
   =============================== */
$resStock = $conn->query("
    SELECT nombre, talla, cantidad, stock_minimo
    FROM productos
    WHERE cantidad <= stock_minimo AND estado = 1
    ORDER BY cantidad ASC
    LIMIT 5
");

/* ===============================
   TOP PRODUCTOS MÁS VENDIDOS
   =============================== */
$resTop = $conn->query("
    SELECT p.nombre, p.talla, SUM(dv.cantidad) AS total_vendido, SUM(dv.subtotal) AS total_ingresos
    FROM detalle_venta dv
    INNER JOIN productos p ON dv.id_producto = p.id_producto
    GROUP BY dv.id_producto
    ORDER BY total_vendido DESC
    LIMIT 5
");
$topProductos = [];
if ($resTop) {
    while ($row = $resTop->fetch_assoc()) {
        $topProductos[] = $row;
    }
}
$maxVendido = !empty($topProductos) ? $topProductos[0]['total_vendido'] : 1;
?>

<!-- PÁGINA: DASHBOARD -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-700 mb-1" style="color:#1a2942; font-weight:700;">Dashboard</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Resumen general del sistema · <?= date('d F Y') ?></p>
    </div>
</div>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="stat-label">Productos en inventario</div>
                <div class="stat-value"><?= $total_productos ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-red">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="stat-label">Bajo stock</div>
                <div class="stat-value"><?= $productos_bajos ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-label">Clientes registrados</div>
                <div class="stat-value"><?= $total_clientes ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-orange">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div>
                <div class="stat-label">Ventas hoy</div>
                <div class="stat-value">$<?= number_format($ventas_hoy, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- GRÁFICAS -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-bar-chart-line text-primary"></i>
                Ventas de la semana
            </div>
            <div class="card-body p-3">
                <canvas id="graf_semana" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle text-danger"></i>
                Productos con bajo stock
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Mín.</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($resStock->num_rows > 0): ?>
                        <?php while($p = $resStock->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="fw-500"><?= htmlspecialchars($p['nombre']) ?></span>
                                <?php if($p['talla']): ?>
                                <span class="badge bg-light text-secondary ms-1"><?= $p['talla'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger"><?= $p['cantidad'] ?></span>
                            </td>
                            <td class="text-center text-muted"><?= $p['stock_minimo'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">
                            <i class="bi bi-check-circle text-success me-1"></i>Todo en orden
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-graph-up text-success"></i>
                Ventas del mes
            </div>
            <div class="card-body p-3">
                <canvas id="graf_mes" height="130"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-currency-dollar text-warning"></i>
                Ganancias por semana
            </div>
            <div class="card-body p-3">
                <canvas id="graf_ganancias" height="130"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-trophy text-warning"></i>
                Top 5 productos más vendidos
            </div>
            <div class="card-body p-3">
                <?php if (!empty($topProductos)): ?>
                <?php foreach ($topProductos as $i => $tp): ?>
                <?php $porcentaje = $maxVendido > 0 ? round(($tp['total_vendido'] / $maxVendido) * 100) : 0; ?>
                <div class="d-flex align-items-center gap-3 mb-3 <?= $i === count($topProductos)-1 ? 'mb-0' : '' ?>">
                    <div class="d-flex align-items-center justify-content-center rounded-circle"
                         style="width:28px;height:28px;flex-shrink:0;font-size:0.8rem;font-weight:700;
                                background:<?= $i === 0 ? '#fef3c7' : '#f0f2f5' ?>;
                                color:<?= $i === 0 ? '#b45309' : '#64748b' ?>;">
                        <?= $i + 1 ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:0.85rem;font-weight:600;color:#1a2942;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= htmlspecialchars($tp['nombre']) ?>
                                <?= $tp['talla'] ? '<span class="badge bg-light text-secondary border ms-1">'.$tp['talla'].'</span>' : '' ?>
                            </span>
                            <span style="font-size:0.8rem;font-weight:700;color:#2563eb;white-space:nowrap;margin-left:8px;">
                                <?= $tp['total_vendido'] ?> vendidos
                            </span>
                        </div>
                        <div class="progress" style="height:6px;border-radius:6px;background:#f0f2f5;">
                            <div class="progress-bar" style="width:<?= $porcentaje ?>%;background:linear-gradient(90deg,#2563eb,#60a5fa);border-radius:6px;"></div>
                        </div>
                    </div>
                    <span class="text-muted" style="font-size:0.775rem;white-space:nowrap;">
                        $<?= number_format($tp['total_ingresos'], 0, ',', '.') ?>
                    </span>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-graph-up d-block mb-2" style="font-size:1.8rem;color:#cbd5e1;"></i>
                    Aún no hay ventas registradas para mostrar el ranking.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = '#64748b';

const semanaDatos  = <?= json_encode($datosSemana) ?>;
const mesLabels    = <?= json_encode($labelsMes) ?>;
const mesDatos     = <?= json_encode($datosMes) ?>;
const ganLabels    = <?= json_encode($labelsGanancias) ?>;
const ganDatos     = <?= json_encode($datosGanancias) ?>;

const gridOpts = {
    color: '#f1f5f9',
    drawBorder: false
};

// Ventas semana
new Chart(document.getElementById('graf_semana'), {
    type: 'bar',
    data: {
        labels: ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'],
        datasets: [{
            label: 'Ventas ($)',
            data: semanaDatos,
            backgroundColor: '#2563eb',
            borderRadius: 6,
            borderSkipped: false,
            hoverBackgroundColor: '#1d4ed8'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: gridOpts, ticks: { font: { size: 11 } } },
            y: { grid: gridOpts, beginAtZero: true, ticks: { font: { size: 11 } } }
        }
    }
});

// Ventas mes
new Chart(document.getElementById('graf_mes'), {
    type: 'line',
    data: {
        labels: mesLabels,
        datasets: [{
            label: 'Ventas ($)',
            data: mesDatos,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: '#10b981',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: gridOpts, ticks: { font: { size: 11 } } },
            y: { grid: gridOpts, beginAtZero: true, ticks: { font: { size: 11 } } }
        }
    }
});

// Ganancias
new Chart(document.getElementById('graf_ganancias'), {
    type: 'bar',
    data: {
        labels: ganLabels,
        datasets: [{
            label: 'Ganancias ($)',
            data: ganDatos,
            backgroundColor: '#f59e0b',
            borderRadius: 6,
            borderSkipped: false,
            hoverBackgroundColor: '#d97706'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: gridOpts, ticks: { font: { size: 11 } } },
            y: { grid: gridOpts, beginAtZero: true, ticks: { font: { size: 11 } } }
        }
    }
});
</script>

<?php include "../includes/footer.php"; ?>
