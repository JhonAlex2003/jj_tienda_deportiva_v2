<?php
include_once 'controllers/ClienteController.php';

$controller = new ClienteController();
$clientes = $controller->listar();

echo "<h2>Lista de clientes</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th></tr>";

foreach ($clientes as $c) {
    echo "<tr>";
    echo "<td>{$c['id']}</td>";
    echo "<td>{$c['nombre']}</td>";
    echo "<td>{$c['correo']}</td>";
    echo "<td>{$c['telefono']}</td>";
    echo "</tr>";
}
echo "</table>";
?>
