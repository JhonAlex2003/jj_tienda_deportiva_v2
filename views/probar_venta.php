<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/VentasController.php';

$controller = new VentasController($conn);

// Productos de ejemplo
$productos = [
    ['id_producto' => 1, 'cantidad' => 2, 'precio_unitario' => 5000],
    ['id_producto' => 3, 'cantidad' => 1, 'precio_unitario' => 12000]
];

// Registrar venta sin cliente
$id_venta = $controller->registrarVenta(null, $productos);

echo "✅ Venta registrada con ID: $id_venta";

// Obtener historial completo
$historial = $controller->obtenerHistorialVentas();
echo "<pre>";
print_r($historial);
echo "</pre>";
?>


