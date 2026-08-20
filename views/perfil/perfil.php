<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../controllers/usuariosController.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: /jj_tienda_deportiva/views/login.php");
    exit();
}

$controller = new UsuariosController();
$mensaje = '';
$tipo    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'nombre') {
        $nombre = trim($_POST['nombre']);
        if ($controller->actualizarNombre($_SESSION['id_usuario'], $nombre)) {
            $mensaje = "Nombre actualizado correctamente.";
            $tipo    = 'success';
        } else {
            $mensaje = "Error al actualizar el nombre.";
            $tipo    = 'danger';
        }
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'password') {
        $actual = $_POST['password_actual'];
        $nueva  = $_POST['password_nueva'];
        $confirmar = $_POST['password_confirmar'];

        if ($nueva !== $confirmar) {
            $mensaje = "La nueva contraseña y su confirmación no coinciden.";
            $tipo    = 'danger';
        } else {
            $resultado = $controller->cambiarPassword($_SESSION['id_usuario'], $actual, $nueva);
            if ($resultado['ok']) {
                $mensaje = "Contraseña actualizada correctamente.";
                $tipo    = 'success';
            } else {
                $mensaje = $resultado['error'];
                $tipo    = 'danger';
            }
        }
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'pregunta_seguridad') {
        $pregunta  = trim($_POST['pregunta']);
        $respuesta = trim($_POST['respuesta']);

        if (empty($pregunta) || empty($respuesta)) {
            $mensaje = "Debes completar la pregunta y la respuesta.";
            $tipo    = 'danger';
        } else {
            if ($controller->configurarPreguntaSeguridad($_SESSION['id_usuario'], $pregunta, $respuesta)) {
                $mensaje = "Pregunta de seguridad guardada correctamente.";
                $tipo    = 'success';
            } else {
                $mensaje = "Error al guardar la pregunta de seguridad.";
                $tipo    = 'danger';
            }
        }
    }
}

$perfil = $controller->obtenerPerfil($_SESSION['id_usuario']);

include __DIR__ . '/../../includes/layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Mi Perfil</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Gestiona tu información y contraseña</p>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
<div class="alert alert-<?= $tipo ?> d-flex align-items-center gap-2 mb-4" style="border-radius:10px;">
    <i class="bi bi-<?= $tipo === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= $mensaje ?>
</div>
<?php endif; ?>

<div class="row justify-content-center g-4">

    <!-- Tarjeta de identidad -->
    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-body p-4">
                <div class="position-relative mx-auto mb-3" style="width:80px;">
                    <?php if (!empty($perfil['foto_perfil'])): ?>
                    <img src="<?= htmlspecialchars($perfil['foto_perfil']) ?>?v=<?= time() ?>" alt="Foto de perfil"
                         class="rounded-circle" style="width:80px;height:80px;object-fit:cover;border:3px solid #e2e8f0;">
                    <?php else: ?>
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                         style="width:80px;height:80px;font-size:1.8rem;font-weight:700;">
                        <?= strtoupper(substr($perfil['nombre'] ?? $perfil['usuario'], 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                    <label for="input-foto" class="position-absolute d-flex align-items-center justify-content-center"
                           style="bottom:-2px;right:-2px;width:28px;height:28px;background:#2563eb;border-radius:50%;
                                  cursor:pointer;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                        <i class="bi bi-camera-fill text-white" style="font-size:0.7rem;"></i>
                    </label>
                    <input type="file" id="input-foto" accept="image/*" class="d-none">
                </div>
                <h6 class="fw-bold mb-1" style="color:#1a2942;">
                    <?= htmlspecialchars($perfil['nombre'] ?? $perfil['usuario']) ?>
                </h6>
                <p class="text-muted mb-2" style="font-size:0.825rem;">@<?= htmlspecialchars($perfil['usuario']) ?></p>
                <span class="badge bg-primary">
                    <?= $perfil['rol'] === 'admin' ? 'Administrador' : 'Vendedor' ?>
                </span>
                <hr class="my-3">
                <p class="text-muted mb-0" style="font-size:0.775rem;">
                    <i class="bi bi-calendar3 me-1"></i>
                    Miembro desde <?= date('d/m/Y', strtotime($perfil['fecha_registro'])) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Formularios -->
    <div class="col-lg-6">
        <!-- Nombre -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person me-2 text-primary"></i>Información personal
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="accion" value="nombre">
                    <div class="mb-3">
                        <label class="form-label">Nombre para mostrar</label>
                        <input type="text" name="nombre" class="form-control"
                               value="<?= htmlspecialchars($perfil['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($perfil['usuario']) ?>" disabled>
                        <small class="text-muted">El nombre de usuario no se puede modificar.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Guardar cambios
                    </button>
                </form>
            </div>
        </div>

        <!-- Contraseña -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-lock me-2 text-warning"></i>Cambiar contraseña
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="accion" value="password">
                    <div class="mb-3">
                        <label class="form-label">Contraseña actual <span class="text-danger">*</span></label>
                        <input type="password" name="password_actual" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nueva contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password_nueva" class="form-control" minlength="6" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmar" class="form-control" minlength="6" required>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2 mb-3">Mínimo 6 caracteres.</small>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-check me-1"></i> Actualizar contraseña
                    </button>
                </form>
            </div>
        </div>

        <!-- Pregunta de seguridad -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-patch-question me-2 text-primary"></i>Pregunta de seguridad
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-3" style="font-size:0.825rem;">
                    Se usa para recuperar tu contraseña si la olvidas, sin depender de otra persona.
                </p>
                <?php if (!empty($perfil['pregunta_seguridad'])): ?>
                <div class="alert alert-info d-flex align-items-center gap-2 mb-3" style="border-radius:10px;font-size:0.825rem;">
                    <i class="bi bi-check-circle"></i>
                    Ya tienes configurada: "<?= htmlspecialchars($perfil['pregunta_seguridad']) ?>"
                </div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="accion" value="pregunta_seguridad">
                    <div class="mb-3">
                        <label class="form-label">Pregunta <span class="text-danger">*</span></label>
                        <select name="pregunta" class="form-select" required>
                            <option value="">-- Selecciona una pregunta --</option>
                            <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
                            <option value="¿En qué ciudad naciste?">¿En qué ciudad naciste?</option>
                            <option value="¿Cuál es tu comida favorita?">¿Cuál es tu comida favorita?</option>
                            <option value="¿Cuál es el nombre de tu mejor amigo de la infancia?">¿Cuál es el nombre de tu mejor amigo de la infancia?</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Respuesta <span class="text-danger">*</span></label>
                        <input type="text" name="respuesta" class="form-control" placeholder="Tu respuesta secreta" required>
                        <small class="text-muted">No distingue mayúsculas/minúsculas.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Guardar pregunta
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de recorte de foto -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
<div class="modal fade" id="modalRecorte" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:#1a2942;">
                    <i class="bi bi-crop me-2 text-primary"></i>Ajustar foto de perfil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div style="max-height:400px;overflow:hidden;">
                    <img id="imagen-recorte" src="" style="max-width:100%;display:block;">
                </div>
                <small class="text-muted d-block mt-2">Arrastra y ajusta el recuadro para encuadrar tu foto.</small>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-foto">
                    <i class="bi bi-check-circle me-1"></i> Guardar foto
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
let cropper = null;
const inputFoto = document.getElementById('input-foto');
const imagenRecorte = document.getElementById('imagen-recorte');
const modalRecorte = new bootstrap.Modal(document.getElementById('modalRecorte'));

inputFoto.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('Por favor selecciona un archivo de imagen válido.');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(evt) {
        imagenRecorte.src = evt.target.result;
        modalRecorte.show();
    };
    reader.readAsDataURL(file);
});

document.getElementById('modalRecorte').addEventListener('shown.bs.modal', function() {
    if (cropper) cropper.destroy();
    cropper = new Cropper(imagenRecorte, {
        aspectRatio: 1,
        viewMode: 1,
        autoCropArea: 1,
        background: false,
    });
});

document.getElementById('modalRecorte').addEventListener('hidden.bs.modal', function() {
    if (cropper) { cropper.destroy(); cropper = null; }
    inputFoto.value = '';
});

document.getElementById('btn-guardar-foto').addEventListener('click', function() {
    if (!cropper) return;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

    const canvas = cropper.getCroppedCanvas({ width: 300, height: 300 });
    const imagenBase64 = canvas.toDataURL('image/jpeg', 0.9);

    fetch('/jj_tienda_deportiva/views/perfil/actualizar_foto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ imagen: imagenBase64 })
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Guardar foto';
        }
    })
    .catch(() => {
        alert('Error de conexión al guardar la foto.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Guardar foto';
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
