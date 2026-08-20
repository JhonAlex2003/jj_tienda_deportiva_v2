<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductosController.php';

$db = new Database();
$conn = $db->connect();
$controller = new ProductosController($conn);

$id = $_GET['id'] ?? null;
$producto = $controller->obtener($id);

if (!$producto) {
    include __DIR__ . '/../../includes/layout.php';
    echo "<div class='alert alert-danger'>Producto no encontrado.</div>";
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $controller->actualizar($id, $_POST);
    header("Location: listar_productos.php");
    exit();
}

$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Editar Producto</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">
            Modificando: <strong><?= htmlspecialchars($producto['nombre']) ?></strong>
        </p>
    </div>
    <a href="listar_productos.php" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil me-2 text-warning"></i>Información del producto
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre del producto <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                   value="<?= htmlspecialchars($producto['nombre']) ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select name="categoria" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>"
                                    <?= $producto['categoria'] == $cat['id_categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Talla</label>
                            <select name="talla" class="form-select">
                                <option value="">Sin talla</option>
                                <?php foreach(['XS','S','M','L','XL','XXL'] as $t): ?>
                                <option value="<?= $t ?>" <?= $producto['talla'] == $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Precio <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="precio" class="form-control"
                                       value="<?= $producto['precio'] ?>" min="0" step="100" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" name="cantidad" class="form-control"
                                   value="<?= $producto['cantidad'] ?>" min="0" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Stock mínimo</label>
                            <input type="number" name="stock_minimo" class="form-control"
                                   value="<?= $producto['stock_minimo'] ?? 5 ?>" min="1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="1" <?= ($producto['estado'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= ($producto['estado'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-warning me-2">
                                <i class="bi bi-check-circle me-1"></i> Actualizar producto
                            </button>
                            <a href="listar_productos.php" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
