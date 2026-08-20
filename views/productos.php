<?php
include_once __DIR__ . '/../controllers/ProductoController.php';
$controller = new ProductoController();
$productos = $controller->listar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">Gestión de Productos</h2>
    <div class="d-flex justify-content-end mb-3">
        <a href="agregar_producto.php" class="btn btn-success">Agregar Producto</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Categoría</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($productos as $producto): ?>
            <tr>
                <td><?= $producto['id']; ?></td>
                <td><?= $producto['nombre']; ?></td>
                <td><?= $producto['descripcion']; ?></td>
                <td>$<?= number_format($producto['precio'], 2); ?></td>
                <td><?= $producto['stock']; ?></td>
                <td><?= $producto['categoria']; ?></td>
                <td>
                    <a href="actualizar_producto.php?id=<?= $producto['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="eliminar_producto.php?id=<?= $producto['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este producto?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
