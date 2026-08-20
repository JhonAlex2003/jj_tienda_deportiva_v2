<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/clientesController.php';

// Verificar que se recibió el ID
if (!isset($_GET['id'])) {
    header('Location: listar_clientes.php');
    exit;
}

$id = $_GET['id'];

// Crear la conexión
$db = new Database();
$conn = $db->connect();

// Instanciar el controlador correctamente
$controller = new ClientesController($conn);

// Eliminar el cliente
$controller->eliminar($id);

// Redirigir a la lista de clientes
header('Location: listar_clientes.php');
exit;
?>
