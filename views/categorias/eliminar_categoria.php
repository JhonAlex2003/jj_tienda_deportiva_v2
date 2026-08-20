<?php
require_once "../../config/db.php";
require_once "../../controllers/categoriasController.php";

$db = new Database();
$conn = $db->connect();
$controller = new CategoriasController($conn);

if (!isset($_GET['id'])) {
    header("Location: listar_categorias.php");
    exit;
}

$id = $_GET['id'];
$controller->eliminar($id);

header("Location: listar_categorias.php");
exit;
?>
