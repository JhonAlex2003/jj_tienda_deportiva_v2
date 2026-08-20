<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario'])) {
    header("Location: /jj_tienda_deportiva/views/login.php");
    exit();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ventasController.php';

$db = new Database();
$conn = $db->connect();
$controller = new VentasController($conn);

$id_venta = $_GET['id'] ?? null;
if (!$id_venta) {
    die("Venta no especificada.");
}

// Obtener datos de la venta
$stmt = $conn->prepare("
    SELECT v.id_venta, v.fecha_venta, v.total, v.estado_pago,
           c.nombre AS cliente, c.telefono, c.correo
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
    WHERE v.id_venta = ?
");
$stmt->bind_param("i", $id_venta);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();

if (!$venta) {
    die("Venta no encontrada.");
}

$detalle = $controller->obtenerDetalleVenta($id_venta);

// Calcular abonado si aplica
$abonado = 0;
if ($venta['estado_pago'] !== 'pagado') {
    $stmtA = $conn->prepare("SELECT COALESCE(SUM(monto),0) AS total FROM abonos WHERE id_venta = ?");
    $stmtA->bind_param("i", $id_venta);
    $stmtA->execute();
    $abonado = $stmtA->get_result()->fetch_assoc()['total'];
}
$saldo = $venta['total'] - $abonado;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comprobante de Venta #<?= $id_venta ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
body { background: #f0f2f5; padding: 2rem; display: flex; justify-content: center; }
.recibo {
    background: #fff;
    width: 380px;
    padding: 1.75rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.header { text-align: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px dashed #e2e8f0; }
.header h1 { font-size: 1.1rem; font-weight: 700; color: #1a2942; }
.header p { font-size: 0.7rem; color: #94a3b8; margin-top: 2px; }
.info-row { display: flex; justify-content: space-between; font-size: 0.75rem; color: #475569; margin-bottom: 4px; }
.info-row span:last-child { font-weight: 600; color: #1a2942; }
.divider { border-top: 1px dashed #e2e8f0; margin: 0.85rem 0; }
table { width: 100%; font-size: 0.75rem; border-collapse: collapse; }
th { text-align: left; color: #94a3b8; font-weight: 600; padding-bottom: 6px; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.4px; }
td { padding: 4px 0; color: #1a2942; }
td.num, th.num { text-align: right; }
.total-row { display: flex; justify-content: space-between; font-size: 0.95rem; font-weight: 700; color: #1a2942; margin-top: 0.5rem; }
.saldo-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.6rem 0.8rem; margin-top: 0.75rem; font-size: 0.75rem; color: #dc2626; display: flex; justify-content: space-between; }
.badge-estado { display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; }
.badge-pagado { background: #dcfce7; color: #16a34a; }
.badge-abono { background: #fef3c7; color: #b45309; }
.badge-pendiente { background: #fee2e2; color: #dc2626; }
.footer-nota { text-align: center; font-size: 0.68rem; color: #94a3b8; margin-top: 1.25rem; padding-top: 0.85rem; border-top: 2px dashed #e2e8f0; }
.acciones { max-width: 380px; margin: 1rem auto 0; display: flex; gap: 0.5rem; }
.btn { flex: 1; padding: 0.6rem; border: none; border-radius: 8px; font-size: 0.825rem; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; }
.btn-print { background: #2563eb; color: #fff; }
.btn-back { background: #e2e8f0; color: #475569; }
@media print {
    body { background: #fff; padding: 0; }
    .acciones { display: none; }
    .recibo { box-shadow: none; }
}
</style>
</head>
<body>
<div>
    <div class="recibo">
        <div class="header">
            <h1>JJ TIENDA DEPORTIVA</h1>
            <p>Comprobante de venta · No es factura electrónica oficial</p>
        </div>

        <div class="info-row"><span>N° de venta:</span><span>#<?= $venta['id_venta'] ?></span></div>
        <div class="info-row"><span>Fecha:</span><span><?= date('d/m/Y H:i', strtotime($venta['fecha_venta'])) ?></span></div>
        <div class="info-row"><span>Cliente:</span><span><?= htmlspecialchars($venta['cliente'] ?? 'Consumidor final') ?></span></div>
        <?php if ($venta['telefono']): ?>
        <div class="info-row"><span>Teléfono:</span><span><?= htmlspecialchars($venta['telefono']) ?></span></div>
        <?php endif; ?>

        <div class="divider"></div>

        <table>
            <thead>
                <tr><th>Producto</th><th class="num">Cant.</th><th class="num">Subtotal</th></tr>
            </thead>
            <tbody>
            <?php foreach ($detalle as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['producto_nombre']) ?><?= $d['talla'] ? ' ('.$d['talla'].')' : '' ?></td>
                    <td class="num"><?= $d['cantidad'] ?></td>
                    <td class="num">$<?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="divider"></div>

        <div class="total-row">
            <span>TOTAL</span>
            <span>$<?= number_format($venta['total'], 0, ',', '.') ?></span>
        </div>

        <div class="info-row" style="margin-top:0.6rem;">
            <span>Estado del pago:</span>
            <span>
                <?php
                $badges = ['pagado' => 'badge-pagado', 'abono' => 'badge-abono', 'pendiente' => 'badge-pendiente'];
                $labels = ['pagado' => 'Pagado', 'abono' => 'Abono', 'pendiente' => 'Pendiente'];
                $cls = $badges[$venta['estado_pago']] ?? '';
                ?>
                <span class="badge-estado <?= $cls ?>"><?= $labels[$venta['estado_pago']] ?? $venta['estado_pago'] ?></span>
            </span>
        </div>

        <?php if ($venta['estado_pago'] !== 'pagado' && $saldo > 0): ?>
        <div class="saldo-box">
            <span>Saldo pendiente:</span>
            <strong>$<?= number_format($saldo, 0, ',', '.') ?></strong>
        </div>
        <?php endif; ?>

        <div class="footer-nota">
            Gracias por tu compra<br>
            Este comprobante es un documento interno de venta
        </div>
    </div>

    <div class="acciones">
        <a href="/jj_tienda_deportiva/views/ventas/historial_ventas.php" class="btn btn-back">Volver</a>
        <button onclick="window.print()" class="btn btn-print">Imprimir / Guardar PDF</button>
    </div>
</div>
</body>
</html>
