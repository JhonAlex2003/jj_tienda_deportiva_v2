<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/clientesController.php';

// Crear la conexión
$db = new Database();
$conn = $db->connect();

// Instanciar el controlador
$controller = new ClientesController($conn);
$error = '';

// Verificar que se recibió el ID
if (!isset($_GET['id'])) {
    header('Location: listar_clientes.php');
    exit;
}

$id = $_GET['id'];
$cliente = $controller->obtener($id);

if (!$cliente) {
    header('Location: listar_clientes.php');
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = [
        'nombre' => $_POST['nombre'],
        'telefono' => $_POST['telefono'],
        'correo' => $_POST['correo'],
        'direccion' => $_POST['direccion']
    ];

    if ($controller->actualizar($id, $datos)) {
        // Redirigir si se actualizó correctamente
        header('Location: listar_clientes.php');
        exit;
    } else {
        $error = "Error al actualizar cliente.";
        // Recargar los datos del cliente desde la base para mostrarlos actualizados
        $cliente = $controller->obtener($id);
    }
}
?>

<?php include __DIR__ . '/../../includes/layout.php'; ?>

<!-- CONTENIDO DE ACTUALIZAR CLIENTE -->
<div class="p-4">
    <h2>Actualizar Cliente</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($cliente['nombre']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($cliente['telefono']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Correo</label>
            <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($cliente['correo']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Dirección</label>
            <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($cliente['direccion']); ?>" required>
        </div>
        <button type="submit" class="btn btn-warning">Actualizar</button>
        <a href="listar_clientes.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

</body>
</html>

