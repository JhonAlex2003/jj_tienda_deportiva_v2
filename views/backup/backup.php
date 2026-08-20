<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario'])) {
    header("Location: /jj_tienda_deportiva/views/login.php");
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$db = new Database();
$conn = $db->connect();

$mensaje = '';
$tipo    = '';

// Generar y descargar el backup
if (isset($_GET['descargar']) && $_GET['descargar'] === '1') {
    $dbName = 'jj_tienda_db';
    $tablas = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tablas[] = $row[0];
    }

    $sql = "-- =====================================================\n";
    $sql .= "-- Backup de $dbName\n";
    $sql .= "-- Generado el " . date('d/m/Y H:i:s') . " desde JJ Tienda Deportiva\n";
    $sql .= "-- =====================================================\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tablas as $tabla) {
        // Estructura
        $res = $conn->query("SHOW CREATE TABLE `$tabla`");
        $row = $res->fetch_row();
        $sql .= "-- --------------------------------------------------------\n";
        $sql .= "-- Estructura de tabla `$tabla`\n";
        $sql .= "-- --------------------------------------------------------\n\n";
        $sql .= "DROP TABLE IF EXISTS `$tabla`;\n";
        $sql .= $row[1] . ";\n\n";

        // Datos
        $resData = $conn->query("SELECT * FROM `$tabla`");
        if ($resData && $resData->num_rows > 0) {
            $sql .= "-- Volcado de datos para la tabla `$tabla`\n\n";
            while ($fila = $resData->fetch_assoc()) {
                $campos = array_keys($fila);
                $valores = array_map(function($v) use ($conn) {
                    if ($v === null) return 'NULL';
                    return "'" . $conn->real_escape_string($v) . "'";
                }, array_values($fila));

                $sql .= "INSERT INTO `$tabla` (`" . implode('`, `', $campos) . "`) VALUES (" . implode(', ', $valores) . ");\n";
            }
            $sql .= "\n";
        }
    }

    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

    $nombreArchivo = "backup_jj_tienda_" . date('Ymd_His') . ".sql";

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Content-Length: ' . strlen($sql));
    echo $sql;
    exit;
}

// Estadísticas para mostrar en pantalla
$stats = [];
$tablasInfo = ['productos', 'categorias', 'clientes', 'ventas', 'detalle_venta', 'usuarios', 'movimientos_inventario', 'abonos'];
foreach ($tablasInfo as $t) {
    $res = $conn->query("SELECT COUNT(*) as total FROM `$t`");
    $stats[$t] = $res ? $res->fetch_assoc()['total'] : 0;
}

include __DIR__ . '/../../includes/layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Respaldo de la Base de Datos</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Descarga una copia completa de toda la información del sistema</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-check me-2 text-primary"></i>Generar respaldo
            </div>
            <div class="card-body p-4">
                <p style="font-size:0.875rem;color:#475569;line-height:1.6;">
                    Este proceso genera un archivo <strong>.sql</strong> con toda la estructura y los datos
                    actuales del sistema: productos, categorías, clientes, ventas, movimientos de inventario,
                    abonos y usuarios.
                </p>
                <div class="alert alert-warning d-flex align-items-start gap-2" style="border-radius:10px;font-size:0.825rem;">
                    <i class="bi bi-info-circle mt-1"></i>
                    <span>Guarda este archivo en un lugar seguro (USB, correo, Drive). Si algo llega a fallar en el computador, este respaldo permite restaurar toda la información.</span>
                </div>
                <a href="?descargar=1" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-3">
                    <i class="bi bi-download"></i> Descargar respaldo completo (.sql)
                </a>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-arrow-clockwise me-2 text-warning"></i>Cómo restaurar un respaldo
            </div>
            <div class="card-body p-4">
                <ol style="font-size:0.85rem;color:#475569;line-height:1.9;padding-left:1.2rem;">
                    <li>Abre <strong>phpMyAdmin</strong> (<code>http://localhost/phpmyadmin</code>)</li>
                    <li>Selecciona la base de datos <strong>jj_tienda_db</strong></li>
                    <li>Ve a la pestaña <strong>Importar</strong></li>
                    <li>Selecciona el archivo <code>.sql</code> del respaldo</li>
                    <li>Haz clic en <strong>Continuar</strong></li>
                </ol>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-database me-2 text-primary"></i>Contenido actual del sistema
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <tr>
                            <td><i class="bi bi-box-seam me-2 text-muted"></i>Productos</td>
                            <td class="text-end fw-bold"><?= $stats['productos'] ?></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-tags me-2 text-muted"></i>Categorías</td>
                            <td class="text-end fw-bold"><?= $stats['categorias'] ?></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-people me-2 text-muted"></i>Clientes</td>
                            <td class="text-end fw-bold"><?= $stats['clientes'] ?></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-cart-check me-2 text-muted"></i>Ventas</td>
                            <td class="text-end fw-bold"><?= $stats['ventas'] ?></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-list-ul me-2 text-muted"></i>Detalles de venta</td>
                            <td class="text-end fw-bold"><?= $stats['detalle_venta'] ?></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-clock-history me-2 text-muted"></i>Movimientos de inventario</td>
                            <td class="text-end fw-bold"><?= $stats['movimientos_inventario'] ?></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-cash-coin me-2 text-muted"></i>Abonos registrados</td>
                            <td class="text-end fw-bold"><?= $stats['abonos'] ?></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-person-badge me-2 text-muted"></i>Usuarios del sistema</td>
                            <td class="text-end fw-bold"><?= $stats['usuarios'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
