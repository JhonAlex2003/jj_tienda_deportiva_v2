<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/reportesController.php';

// Crear conexión
$db = new Database();
$conn = $db->connect();

// Instanciar controlador
$controller = new ReportesController($conn);

// Obtener fechas
$fechas = $controller->obtenerFechas();
$resultado = [];

$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? null;

if ($fecha_inicio && $fecha_fin) {
    $resultado = $controller->ventasPorRango($fecha_inicio, $fecha_fin);

    // Exportar a Excel si se presionó el botón
    if(isset($_POST['exportar']) && $_POST['exportar'] === 'excel' && !empty($resultado)) {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Reporte_Ventas_".date('Ymd').".xls");
        echo "<table border='1'>";
        echo "<tr><th>ID Venta</th><th>Fecha</th><th>Cliente</th><th>Productos</th><th>Total</th></tr>";
        foreach($resultado as $row){
            echo "<tr>
                    <td>{$row['id_venta']}</td>
                    <td>{$row['fecha_venta']}</td>
                    <td>{$row['cliente']}</td>
                    <td>{$row['productos']}</td>
                    <td>{$row['total']}</td>
                  </tr>";
        }
        echo "</table>";
        exit;
    }
}

?>

<?php include __DIR__ . '/../../includes/layout.php'; ?>

<div class="p-4">
    <h2 class="mb-4">Reportes de Ventas</h2>

    <!-- Selección de rango de fechas -->
    <form method="POST" class="card p-4 shadow mb-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Fecha Inicio:</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha Fin:</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>" required>
            </div>
        </div>
        <button class="btn btn-primary mt-3">Generar reporte</button>
        <button type="submit" name="exportar" value="excel" class="btn btn-success mt-3 ms-2">Exportar a Excel</button>
    </form>

    <!-- Resultados -->
    <?php if (!empty($resultado)): ?>
        <h4>Resultados de <?= htmlspecialchars($fecha_inicio) ?> a <?= htmlspecialchars($fecha_fin) ?></h4>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Productos</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultado as $row): ?>
                    <tr>
                        <td><?= $row['id_venta']; ?></td>
                        <td><?= $row['fecha_venta']; ?></td>
                        <td><?= htmlspecialchars($row['cliente']); ?></td>
                        <td><?= htmlspecialchars($row['productos']); ?></td>
                        <td>$<?= number_format($row['total'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($fecha_inicio && $fecha_fin): ?>
        <div class="alert alert-info">No se encontraron ventas para el rango de fechas seleccionado.</div>
    <?php endif; ?>
</div>
</body>
</html>

