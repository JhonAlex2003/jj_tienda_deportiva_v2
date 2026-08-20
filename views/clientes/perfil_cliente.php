<?php
// views/clientes/perfil_cliente.php
include_once __DIR__ . '/../../config/db.php';

if (!isset($_GET['id'])) {
    echo "No se proporcionó un ID de cliente.";
    exit;
}

$id_cliente = intval($_GET['id']);
$sql = "SELECT * FROM clientes WHERE id_cliente = $id_cliente";
$res = $conn->query($sql);

if (!$res || $res->num_rows == 0) {
    echo "Cliente no encontrado.";
    exit;
}

$cliente = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil del Cliente - JJ Tienda Deportiva</title>
    <link rel="stylesheet" href="/jj_tienda_deportiva/assets/css/style.css">
</head>
<body>
    <h1>Perfil del Cliente</h1>

    <div class="perfil-cliente">
        <p><strong>ID:</strong> <?= htmlspecialchars($cliente['id_cliente']) ?></p>
        <p><strong>Nombre:</strong> <?= htmlspecialchars($cliente['nombre']) ?></p>
        <p><strong>Teléfono:</strong> <?= htmlspecialchars($cliente['telefono']) ?></p>
        <p><strong>Correo:</strong> <?= htmlspecialchars($cliente['correo']) ?></p>
        <p><strong>Dirección:</strong> <?= htmlspecialchars($cliente['direccion']) ?></p>
    </div>

    <p>
        <a href="listar_clientes.php">← Volver a la lista</a> |
        <a href="editar_cliente.php?id=<?= $cliente['id_cliente'] ?>">Editar</a> |
        <a href="eliminar_cliente.php?id=<?= $cliente['id_cliente'] ?>" onclick="return confirm('¿Eliminar este cliente?');">Eliminar</a>
    </p>
</body>
</html>

