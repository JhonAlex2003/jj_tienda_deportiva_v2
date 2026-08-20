<?php
// views/clientes/editar_cliente.php
include_once __DIR__ . '/../../config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die('ID inválido. <a href="listar_clientes.php">Volver</a>');
}

$stmt = $conn->prepare("SELECT id_cliente, nombre, telefono, correo, direccion FROM clientes WHERE id_cliente = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die('Cliente no encontrado. <a href="listar_clientes.php">Volver</a>');
}
$cliente = $res->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar Cliente</title>
  <link rel="stylesheet" href="/jj_tienda_deportiva/assets/css/style.css">
</head>
<body>
  <h1>Editar Cliente</h1>
  <form action="actualizar_cliente.php" method="post">
    <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($cliente['id_cliente']) ?>">
    <label>Nombre:<br>
      <input type="text" name="nombre" required maxlength="100" value="<?= htmlspecialchars($cliente['nombre']) ?>">
    </label><br><br>

    <label>Teléfono:<br>
      <input type="text" name="telefono" maxlength="20" value="<?= htmlspecialchars($cliente['telefono']) ?>">
    </label><br><br>

    <label>Correo:<br>
      <input type="email" name="correo" maxlength="100" value="<?= htmlspecialchars($cliente['correo']) ?>">
    </label><br><br>

    <label>Dirección:<br>
      <input type="text" name="direccion" maxlength="150" value="<?= htmlspecialchars($cliente['direccion']) ?>">
    </label><br><br>

    <button type="submit">Actualizar</button>
    <a href="listar_clientes.php">Cancelar</a>
  </form>
</body>
</html>
