<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['admin']);
require_once __DIR__ . '/../../controllers/usuariosController.php';

$controller = new UsuariosController();
$id_actual  = (int)($_SESSION['id_usuario'] ?? 0);

// ---- Procesar acciones (se redirige al terminar para evitar reenvío del formulario) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = ['ok' => false, 'error' => 'Acción no válida.'];

    if (!csrfValido($_POST['csrf'] ?? null)) {
        $resultado = ['ok' => false, 'error' => 'El formulario expiró. Vuelve a intentarlo.'];
    } else {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'crear') {
            if (($_POST['password'] ?? '') !== ($_POST['password_confirmar'] ?? '')) {
                $resultado = ['ok' => false, 'error' => 'La contraseña y su confirmación no coinciden.'];
            } else {
                $resultado = $controller->crearUsuario(
                    $_POST['usuario'] ?? '', $_POST['nombre'] ?? '',
                    $_POST['password'] ?? '', $_POST['rol'] ?? ''
                );
                if ($resultado['ok']) { $resultado['mensaje'] = 'Usuario creado correctamente.'; }
            }
        } elseif ($accion === 'cambiar_rol') {
            $resultado = $controller->cambiarRol((int)($_POST['id_usuario'] ?? 0), $_POST['rol'] ?? '', $id_actual);
            if ($resultado['ok']) { $resultado['mensaje'] = 'Rol actualizado correctamente.'; }
        } elseif ($accion === 'reset_password') {
            $resultado = $controller->restablecerPasswordAdmin((int)($_POST['id_usuario'] ?? 0), $_POST['password'] ?? '');
            if ($resultado['ok']) { $resultado['mensaje'] = 'Contraseña restablecida correctamente.'; }
        } elseif ($accion === 'eliminar') {
            $resultado = $controller->eliminarUsuario((int)($_POST['id_usuario'] ?? 0), $id_actual);
            if ($resultado['ok']) { $resultado['mensaje'] = 'Usuario eliminado correctamente.'; }
        }
    }

    if ($resultado['ok']) {
        $_SESSION['flash_ok'] = $resultado['mensaje'];
    } else {
        $_SESSION['flash_error'] = $resultado['error'];
    }
    header('Location: usuarios.php');
    exit;
}

$usuarios = $controller->listarUsuarios();
include __DIR__ . '/../../includes/layout.php';
$csrf = csrfToken();
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Usuarios</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">
            Administra quién puede ingresar al sistema y con qué rol
        </p>
    </div>
</div>

<div class="row g-4">
    <!-- Crear usuario -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-plus me-2 text-primary"></i>Nuevo usuario</div>
            <div class="card-body p-4">
                <form method="POST" autocomplete="off">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="accion" value="crear">

                    <div class="mb-3">
                        <label class="form-label">Usuario <span class="text-danger">*</span></label>
                        <input type="text" name="usuario" class="form-control" required
                               minlength="3" maxlength="30" pattern="[A-Za-z0-9_.\-]+"
                               title="Letras, números, punto, guion o guion bajo">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre visible <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol <span class="text-danger">*</span></label>
                        <select name="rol" class="form-select" required>
                            <option value="vendedor" selected>Vendedor</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <small class="text-muted">Mínimo 6 caracteres.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmar" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-1"></i> Crear usuario
                    </button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body p-4" style="font-size:0.825rem;color:#475569;">
                <div class="fw-bold mb-2" style="color:#1a2942;">¿Qué puede hacer cada rol?</div>
                <p class="mb-2"><span class="badge bg-primary">Administrador</span>
                    Acceso completo: dashboard, inventario, categorías, movimientos, reportes, respaldo y usuarios.</p>
                <p class="mb-0"><span class="badge bg-secondary">Vendedor</span>
                    Registra ventas y abonos, gestiona clientes y consulta productos y el historial de ventas
                    (sin ver costos ni ganancias).</p>
            </div>
        </div>
    </div>

    <!-- Lista de usuarios -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-people me-2 text-primary"></i>Usuarios registrados</span>
                <span class="badge bg-primary"><?= count($usuarios) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Registro</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <?php $esYo = ((int)$u['id_usuario'] === $id_actual); ?>
                            <tr>
                                <td class="text-muted"><?= (int)$u['id_usuario'] ?></td>
                                <td style="font-weight:600;color:#1a2942;">
                                    <?= htmlspecialchars($u['usuario']) ?>
                                    <?php if ($esYo): ?><span class="badge bg-light text-secondary border ms-1">tú</span><?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($u['nombre'] ?? '') ?></td>
                                <td>
                                    <?php if ($esYo): ?>
                                        <span class="badge bg-primary">Administrador</span>
                                    <?php else: ?>
                                    <form method="POST" class="m-0">
                                        <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                        <input type="hidden" name="accion" value="cambiar_rol">
                                        <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                                        <select name="rol" class="form-select form-select-sm" style="width:140px;"
                                                onchange="this.form.submit()">
                                            <option value="vendedor" <?= $u['rol'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                                            <option value="admin"    <?= $u['rol'] === 'admin'    ? 'selected' : '' ?>>Administrador</option>
                                        </select>
                                    </form>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:0.8rem;color:#64748b;">
                                    <?= !empty($u['fecha_registro']) ? date('d/m/Y', strtotime($u['fecha_registro'])) : '—' ?>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            title="Restablecer contraseña"
                                            data-bs-toggle="modal" data-bs-target="#modalPassword"
                                            data-id="<?= (int)$u['id_usuario'] ?>"
                                            data-usuario="<?= htmlspecialchars($u['usuario'], ENT_QUOTES) ?>">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <?php if (!$esYo): ?>
                                    <form method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?')">
                                        <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: restablecer contraseña -->
<div class="modal fade" id="modalPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <input type="hidden" name="accion" value="reset_password">
            <input type="hidden" name="id_usuario" id="resetId" value="">
            <div class="modal-header">
                <h6 class="modal-title">Restablecer contraseña de <strong id="resetUsuario"></strong></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Nueva contraseña</label>
                <input type="password" name="password" class="form-control" required minlength="6">
                <small class="text-muted">Mínimo 6 caracteres. Entrégasela al usuario para que la cambie desde su perfil.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('modalPassword').addEventListener('show.bs.modal', function (e) {
    document.getElementById('resetId').value = e.relatedTarget.getAttribute('data-id');
    document.getElementById('resetUsuario').textContent = e.relatedTarget.getAttribute('data-usuario');
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
