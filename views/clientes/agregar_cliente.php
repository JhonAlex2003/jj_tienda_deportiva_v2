<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirLogin();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/clientesController.php';

$db = new Database();
$conn = $db->connect();
$controller = new ClientesController($conn);
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = [
        'nombre'    => $_POST['nombre'],
        'telefono'  => $_POST['telefono'],
        'correo'    => $_POST['correo'],
        'direccion' => $_POST['direccion']
    ];
    if ($controller->agregar($datos)) {
        header('Location: listar_clientes.php');
        exit;
    } else {
        $error = "Error al agregar el cliente. Intenta nuevamente.";
    }
}

include __DIR__ . '/../../includes/layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Agregar Cliente</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Registra un nuevo cliente en el sistema</p>
    </div>
    <a href="listar_clientes.php" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 mb-4" style="border-radius:10px;">
    <i class="bi bi-exclamation-circle"></i> <?= $error ?>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus me-2 text-primary"></i>Información del cliente
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                   placeholder="Ej: María González" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="telefono" class="form-control"
                                       placeholder="Ej: 3001234567">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Correo electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="correo" class="form-control"
                                       placeholder="correo@ejemplo.com">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="direccion" class="form-control"
                                       placeholder="Ej: Calle 5B #11-57, Barrio La Cabaña">
                            </div>
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-circle me-1"></i> Guardar cliente
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
