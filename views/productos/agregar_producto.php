<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductosController.php';
include __DIR__ . "/../../includes/layout.php";

$db = new Database();
$conn = $db->connect();
$controller = new ProductosController($conn);
$mensaje = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($controller->agregar($_POST)) {
        $mensaje = "Producto registrado correctamente.";
        $tipo = 'success';
    } else {
        $mensaje = "Error al registrar el producto.";
        $tipo = 'danger';
    }
}

$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Agregar Producto</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Registra un nuevo producto en el inventario</p>
    </div>
    <a href="listar_productos.php" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?php if (!empty($mensaje)): ?>
<div class="alert alert-<?= $tipo ?> d-flex align-items-center gap-2 mb-4" style="border-radius:10px;">
    <i class="bi bi-<?= $tipo == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= $mensaje ?>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-box-seam me-2 text-primary"></i>Información del producto
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre del producto <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Camiseta Deportivo Cali" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"
                                      placeholder="Descripción opcional del producto"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select name="categoria" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Talla</label>
                            <select name="talla" class="form-select">
                                <option value="">Sin talla</option>
                                <option value="XS">XS</option>
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Precio <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="precio" class="form-control"
                                       placeholder="0" min="0" step="100" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" name="cantidad" class="form-control"
                                   placeholder="0" min="0" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Stock mínimo</label>
                            <input type="number" name="stock_minimo" class="form-control"
                                   placeholder="5" min="1" value="5">
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-circle me-1"></i> Guardar producto
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
