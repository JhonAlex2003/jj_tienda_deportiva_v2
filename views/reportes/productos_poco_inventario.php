<?php
include_once __DIR__ . '/../../controllers/reportesController.php';

$controller = new ReportesController();

// Si el usuario quiere cambiar el límite:
$limite = isset($_GET['limite']) ? intval($_GET['limite']) : 5;

$productos = $controller->productosPocoInventario($limite);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos con Poco Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">📉 Productos con Poco Inventario</h2>

    <form method="GET" class="mb-3">
        <label>Mostrar productos con cantidad menor o igual a:</label>
        <input type="number" name="limite" value="<?= $limite ?>" class="form-control" style="max-width:120px;" min="1">
        <button class="btn btn-primary mt-2">Aplicar filtro</button>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Talla</th>
                <th>Precio</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($productos->num_rows > 0): ?>
            <?php while ($p = $productos->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['id_producto'] ?></td>
                    <td><?= $p['nombre'] ?></td>
                    <td><?= $p['categoria'] ?></td>
                    <td><?= $p['talla'] ?></td>
                    <td>$<?= number_format($p['precio'],0) ?></td>
                    <td><strong class="text-danger"><?= $p['cantidad'] ?></strong></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center">No hay productos con poco inventario.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <a href="../menu_reportes.php" class="btn btn-secondary">Volver</a>
</div>

</body>
</html>
