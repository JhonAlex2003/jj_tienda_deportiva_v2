<?php
// Archivo: views/clientes.php

// 1️⃣ Conectamos a la base de datos y cargamos el controlador
include '../config/db.php';
include '../controllers/clientesController.php';

// 2️⃣ Creamos la conexión
$database = new Database();
$db = $database->connect();

// 3️⃣ Creamos el controlador y obtenemos la lista de clientes
$controller = new ClienteController($db);
$clientes = $controller->listarClientes();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Módulo de Clientes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container mt-5">
    <h2 class="text-center mb-4">👥 Gestión de Clientes</h2>

    <!-- Botón para agregar nuevo cliente -->
    <div class="text-end mb-3">
      <a href="agregar_cliente.php" class="btn btn-primary">➕ Agregar Cliente</a>
    </div>

    <!-- Tabla de clientes -->
    <table class="table table-bordered table-striped">
      <thead class="table-dark text-center">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Teléfono</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($clientes && $clientes->rowCount() > 0): ?>
          <?php while ($row = $clientes->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
              <td><?= htmlspecialchars($row['id']) ?></td>
              <td><?= htmlspecialchars($row['nombre']) ?></td>
              <td><?= htmlspecialchars($row['correo']) ?></td>
              <td><?= htmlspecialchars($row['telefono']) ?></td>
              <td class="text-center">
                <a href="editar_cliente.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏️ Editar</a>
                <a href="eliminar_cliente.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este cliente?')">🗑️ Eliminar</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5" class="text-center">No hay clientes registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</body>
</html>
