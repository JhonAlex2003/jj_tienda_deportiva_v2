<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductosController.php';

$db = new Database();
$conn = $db->connect();
$controller = new ProductosController($conn);

$id = $_GET['id'] ?? null;
$resultado = 'error';

if ($id) {
    $resultado = $controller->eliminar($id);
}

header("Location: listar_productos.php?msg=" . urlencode($resultado));
exit();
?>
