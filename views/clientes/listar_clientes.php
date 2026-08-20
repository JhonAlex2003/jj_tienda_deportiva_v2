<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/clientesController.php';

$db = new Database();
$conn = $db->connect();
$controller = new ClientesController($conn);
$clientes = $controller->listar();

include __DIR__ . '/../../includes/layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Clientes</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Gestión de clientes registrados</p>
    </div>
    <a href="agregar_cliente.php" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-person-plus"></i> Agregar Cliente
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
                       placeholder="Buscar cliente..." style="font-size:0.85rem;">
            </div>
            <span class="text-muted" style="font-size:0.825rem;">
                <?= count($clientes) ?> cliente(s) registrado(s)
            </span>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" id="tablaClientes">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($clientes)): ?>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td class="text-muted"><?= $c['id_cliente'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                     style="width:32px;height:32px;font-size:0.8rem;font-weight:600;flex-shrink:0;">
                                    <?= strtoupper(substr($c['nombre'], 0, 1)) ?>
                                </div>
                                <span class="fw-500" style="font-weight:600;color:#1a2942;">
                                    <?= htmlspecialchars($c['nombre']) ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <?php if($c['telefono']): ?>
                            <a href="tel:<?= $c['telefono'] ?>" class="text-decoration-none text-muted">
                                <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($c['telefono']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($c['correo']): ?>
                            <a href="mailto:<?= $c['correo'] ?>" class="text-decoration-none text-muted" style="font-size:0.825rem;">
                                <?= htmlspecialchars($c['correo']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.825rem;color:#64748b;">
                            <?= htmlspecialchars($c['direccion'] ?? '—') ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="actualizar_cliente.php?id=<?= $c['id_cliente'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="eliminar_cliente.php?id=<?= $c['id_cliente'] ?>"
                                   class="btn btn-sm btn-outline-danger" title="Eliminar"
                                   onclick="return confirm('¿Seguro que deseas eliminar este cliente?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people d-block mb-2" style="font-size:2rem;"></i>
                            No hay clientes registrados.
                            <a href="agregar_cliente.php" class="d-block mt-2 text-primary">Agregar el primero</a>
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
    document.querySelectorAll('#tablaClientes tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
