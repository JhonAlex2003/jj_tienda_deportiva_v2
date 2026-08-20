<?php
// views/clientes/guardar_cliente.php
include_once __DIR__ . '/../../config/db.php';

// Validación básica y sanitización
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';

if ($nombre === '') {
    die('El nombre es obligatorio. <a href="agregar_cliente.php">Volver</a>');
}

// Prepared statement para evitar inyección SQL
$stmt = $conn->prepare("INSERT INTO clientes (nombre, telefono, correo, direccion) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    die("Error en la preparación: " . $conn->error);
}
$stmt->bind_param("ssss", $nombre, $telefono, $correo, $direccion);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    header("Location: listar_clientes.php?msg=guardado");
    exit;
} else {
    echo "Error al guardar: " . $conn->error . " <a href='agregar_cliente.php'>Volver</a>";
}
