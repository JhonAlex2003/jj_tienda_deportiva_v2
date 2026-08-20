<?php
require_once "../../config/db.php";
require_once "../../controllers/CategoriasController.php";

$db = new Database();
$conn = $db->connect();
$controller = new CategoriasController($conn);
$mensaje = "";
$tipo = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    if ($controller->crear($nombre, $descripcion)) {
        header("Location: listar_categorias.php");
        exit;
    } else {
        $mensaje = "Error al crear la categoría. Intenta nuevamente.";
        $tipo    = "danger";
    }
}

include "../../includes/layout.php";
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Agregar Categoría</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Registra una nueva categoría de productos</p>
    </div>
    <a href="listar_categorias.php" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-<?= $tipo ?> d-flex align-items-center gap-2 mb-4" style="border-radius:10px;">
    <i class="bi bi-exclamation-circle"></i> <?= $mensaje ?>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-tag me-2 text-primary"></i>Información de la categoría
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                   placeholder="Ej: Fútbol, Pantalonetas, Tenis..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3"
                                      placeholder="Descripción opcional de la categoría"></textarea>
                        </div>
                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-circle me-1"></i> Guardar categoría
                            </button>
                            <a href="listar_categorias.php" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>
