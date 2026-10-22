<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirLogin();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/clientesController.php';

$db = new Database();
$conn = $db->connect();
$controller = new ClientesController($conn);

$id = $_GET['id'] ?? null;
$cliente = $controller->obtener($id);

if (!$cliente) {
    echo "<div class='alert alert-danger'>Cliente no encontrado.</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre'    => $_POST['nombre'],
        'telefono'  => $_POST['telefono'],
        'correo'    => $_POST['correo'],
        'direccion' => $_POST['direccion']
    ];
    $controller->actualizar($id, $datos);
    header('Location: listar_clientes.php');
    exit;
}

include __DIR__ . '/../../includes/layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Editar Cliente</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">
            Modificando: <strong><?= htmlspecialchars($cliente['nombre']) ?></strong>
        </p>
    </div>
    <a href="listar_clientes.php" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil me-2 text-warning"></i>Información del cliente
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                   value="<?= htmlspecialchars($cliente['nombre']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="telefono" class="form-control"
                                       value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Correo electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="correo" class="form-control"
                                       value="<?= htmlspecialchars($cliente['correo'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="direccion" class="form-control"
                                       value="<?= htmlspecialchars($cliente['direccion'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-warning me-2">
                                <i class="bi bi-check-circle me-1"></i> Actualizar cliente
                            </button>
                            <a href="listar_clientes.php" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
