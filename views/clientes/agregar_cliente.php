<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/clientesController.php';

// Crear la conexión
$db = new Database();
$conn = $db->connect();

// Instanciar el controlador pasando la conexión
$controller = new ClientesController($conn);
$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = [
        'nombre' => $_POST['nombre'],
        'telefono' => $_POST['telefono'],
        'correo' => $_POST['correo'],
        'direccion' => $_POST['direccion']
    ];

    if ($controller->agregar($datos)) {
        header('Location: listar_clientes.php');
        exit;
    } else {
        $error = "Error al agregar cliente.";
    }
}
?>

<?php include __DIR__ . '/../../includes/layout.php'; ?>

<!-- CONTENIDO DE AGREGAR CLIENTE -->
<div class="p-4">
    <h2>Agregar Cliente</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Correo</label>
            <input type="email" name="correo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Dirección</label>
            <input type="text" name="direccion" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Agregar</button>
        <a href="listar_clientes.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>


