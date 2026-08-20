<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductosController.php';
include __DIR__ . "/../../includes/layout.php";

$db = new Database();
$conn = $db->connect();
$controller = new ProductosController($conn);
$productos = $controller->listar();
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Productos</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Gestión del inventario de productos</p>
    </div>
    <a href="agregar_producto.php" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-plus-circle"></i> Agregar Producto
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4" style="border-radius:10px;">
        <i class="bi bi-check-circle"></i> Producto eliminado correctamente.
    </div>
    <?php elseif ($_GET['msg'] === 'deactivated'): ?>
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" style="border-radius:10px;">
        <i class="bi bi-exclamation-triangle"></i>
        Este producto tiene ventas o movimientos registrados, por lo que no se puede eliminar sin perder ese historial. En su lugar, se marcó como <strong>Inactivo</strong>.
    </div>
    <?php else: ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" style="border-radius:10px;">
        <i class="bi bi-x-circle"></i> Ocurrió un error al eliminar el producto.
    </div>
    <?php endif; ?>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="p-3 border-bottom d-flex align-items-center gap-3">
            <div class="input-group" style="max-width:280px;">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted" style="font-size:0.85rem;"></i>
                </span>
                <input type="text" id="buscador" class="form-control border-start-0 ps-0"
                       placeholder="Buscar producto..." style="font-size:0.85rem;">
            </div>
            <span class="text-muted" style="font-size:0.825rem;">
                <?= count($productos) ?> producto(s) registrado(s)
            </span>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" id="tablaProductos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Talla</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $p): ?>
                    <?php $bajo = $p['cantidad'] <= ($p['stock_minimo'] ?? 5); ?>
                    <tr>
                        <td class="text-muted"><?= $p['id_producto'] ?></td>
                        <td>
                            <div class="fw-500" style="font-weight:600;color:#1a2942;">
                                <?= htmlspecialchars($p['nombre']) ?>
                            </div>
                            <?php if(!empty($p['descripcion'])): ?>
                            <small class="text-muted"><?= htmlspecialchars(substr($p['descripcion'],0,40)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['categoria']) ?></td>
                        <td>
                            <?php if($p['talla']): ?>
                            <span class="badge bg-light text-secondary border"><?= $p['talla'] ?></span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-500" style="font-weight:600;">
                            $<?= number_format($p['precio'], 0, ',', '.') ?>
                        </td>
                        <td>
                            <?php if($bajo): ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-exclamation-triangle me-1"></i><?= $p['cantidad'] ?>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <?= $p['cantidad'] ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(isset($p['estado']) && $p['estado']): ?>
                            <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="actualizar_producto.php?id=<?= $p['id_producto'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="eliminar_producto.php?id=<?= $p['id_producto'] ?>"
                                   class="btn btn-sm btn-outline-danger" title="Eliminar"
                                   onclick="return confirm('¿Seguro que deseas eliminar este producto?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam d-block mb-2" style="font-size:2rem;"></i>
                            No hay productos registrados.
                            <a href="agregar_producto.php" class="d-block mt-2 text-primary">Agregar el primero</a>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('buscador').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#tablaProductos tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
