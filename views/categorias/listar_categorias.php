<?php
require_once "../../config/db.php";
require_once "../../controllers/CategoriasController.php";

$db = new Database();
$conn = $db->connect();
$controller = new CategoriasController($conn);
$categorias = $controller->listar();

include "../../includes/layout.php";
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Categorías</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Gestión de categorías de productos</p>
    </div>
    <a href="agregar_categoria.php" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-plus-circle"></i> Agregar Categoría
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="p-3 border-bottom d-flex align-items-center gap-3">
            <div class="input-group" style="max-width:280px;">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted" style="font-size:0.85rem;"></i>
                </span>
                <input type="text" id="buscador" class="form-control border-start-0 ps-0"
                       placeholder="Buscar categoría..." style="font-size:0.85rem;">
            </div>
            <span class="text-muted" style="font-size:0.825rem;">
                <?= count($categorias) ?> categoría(s) registrada(s)
            </span>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" id="tablaCategorias">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Fecha de creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($categorias)): ?>
                    <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td class="text-muted"><?= $cat['id_categoria'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded d-flex align-items-center justify-content-center"
                                     style="width:32px;height:32px;background:#eff6ff;flex-shrink:0;">
                                    <i class="bi bi-tag text-primary" style="font-size:0.85rem;"></i>
                                </div>
                                <span style="font-weight:600;color:#1a2942;">
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </span>
                            </div>
                        </td>
                        <td style="font-size:0.825rem;color:#64748b;">
                            <?= htmlspecialchars($cat['descripcion'] ?? '—') ?>
                        </td>
                        <td style="font-size:0.825rem;color:#64748b;">
                            <?= date('d/m/Y', strtotime($cat['fecha_creacion'])) ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="actualizar_categoria.php?id=<?= $cat['id_categoria'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="eliminar_categoria.php?id=<?= $cat['id_categoria'] ?>"
                                   class="btn btn-sm btn-outline-danger" title="Eliminar"
                                   onclick="return confirm('¿Seguro que deseas eliminar esta categoría?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-tags d-block mb-2" style="font-size:2rem;"></i>
                            No hay categorías registradas.
                            <a href="agregar_categoria.php" class="d-block mt-2 text-primary">Agregar la primera</a>
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
    document.querySelectorAll('#tablaCategorias tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>

<?php include "../../includes/footer.php"; ?>
