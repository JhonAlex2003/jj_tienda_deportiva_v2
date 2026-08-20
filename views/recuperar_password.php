<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../controllers/usuariosController.php';

$controller = new UsuariosController();

$paso = 'buscar';
$mensaje = '';
$tipo = '';
$pregunta = null;
$usuario_buscado = $_POST['usuario'] ?? ($_SESSION['recup_usuario'] ?? '');

// Paso 1: buscar usuario y mostrar su pregunta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'buscar') {
    $data = $controller->obtenerPreguntaParaRecuperar($_POST['usuario']);
    if ($data) {
        $_SESSION['recup_usuario'] = $_POST['usuario'];
        $pregunta = $data['pregunta_seguridad'];
        $paso = 'responder';
    } else {
        $mensaje = "No encontramos una pregunta de seguridad configurada para ese usuario. Contacta al administrador del sistema.";
        $tipo = 'danger';
    }
}

// Paso 2: validar respuesta y cambiar contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'restablecer') {
    $usuario = $_SESSION['recup_usuario'] ?? '';
    $respuesta = $_POST['respuesta'];
    $nueva = $_POST['password_nueva'];
    $confirmar = $_POST['password_confirmar'];

    if ($nueva !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo = 'danger';
        $data = $controller->obtenerPreguntaParaRecuperar($usuario);
        $pregunta = $data['pregunta_seguridad'] ?? null;
        $paso = 'responder';
    } else {
        $resultado = $controller->restablecerConRespuesta($usuario, $respuesta, $nueva);
        if ($resultado['ok']) {
            unset($_SESSION['recup_usuario']);
            $paso = 'exito';
        } else {
            $mensaje = $resultado['error'];
            $tipo = 'danger';
            $data = $controller->obtenerPreguntaParaRecuperar($usuario);
            $pregunta = $data['pregunta_seguridad'] ?? null;
            $paso = 'responder';
        }
    }
}

// Si ya hay usuario en sesión de recuperación y venimos de vuelta con error, recuperar pregunta
if ($paso === 'responder' && !$pregunta && !empty($_SESSION['recup_usuario'])) {
    $data = $controller->obtenerPreguntaParaRecuperar($_SESSION['recup_usuario']);
    $pregunta = $data['pregunta_seguridad'] ?? null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Contraseña — JJ Tienda Deportiva</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f2f5;
    padding: 1rem;
}
.card-recuperar {
    background: #fff;
    border-radius: 16px;
    padding: 2.5rem;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}
.icon-header {
    width: 64px; height: 64px;
    border-radius: 16px;
    background: #eff6ff;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.25rem;
}
.icon-header i { font-size: 1.75rem; color: #2563eb; }
h2 { font-size: 1.3rem; font-weight: 700; color: #1a2942; text-align: center; margin-bottom: 0.4rem; }
p.subtitle { font-size: 0.85rem; color: #94a3b8; text-align: center; margin-bottom: 1.75rem; }
.form-label { font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem; display: block; }
.form-control {
    padding: 0.65rem 0.85rem; border: 1.5px solid #e2e8f0; border-radius: 10px;
    font-size: 0.875rem; font-family: 'Inter', sans-serif; margin-bottom: 1rem; width: 100%;
}
.form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); outline: none; }
.btn-main {
    width: 100%; padding: 0.7rem; background: linear-gradient(135deg,#2563eb,#1d4ed8);
    color: #fff; border: none; border-radius: 10px; font-weight: 600; font-size: 0.875rem;
    cursor: pointer; box-shadow: 0 4px 12px rgba(37,99,235,0.3);
}
.btn-main:hover { background: linear-gradient(135deg,#1d4ed8,#1e40af); }
.pregunta-box {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
    padding: 0.85rem 1rem; margin-bottom: 1rem; font-size: 0.875rem; color: #1a2942; font-weight: 500;
}
.alert-custom {
    border-radius: 10px; padding: 0.65rem 0.85rem; font-size: 0.825rem;
    margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;
}
.alert-danger-c { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; }
.alert-success-c { background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; }
.back-link {
    display: block; text-align: center; margin-top: 1.25rem; font-size: 0.825rem;
    color: #64748b; text-decoration: none;
}
.back-link:hover { color: #2563eb; }
</style>
</head>
<body>
<div class="card-recuperar">

    <?php if ($paso === 'buscar'): ?>
        <div class="icon-header"><i class="bi bi-shield-lock"></i></div>
        <h2>Recuperar contraseña</h2>
        <p class="subtitle">Ingresa tu usuario para continuar</p>

        <?php if ($mensaje): ?>
        <div class="alert-custom alert-<?= $tipo ?>-c">
            <i class="bi bi-exclamation-circle"></i> <?= $mensaje ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="accion" value="buscar">
            <label class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control" placeholder="Tu nombre de usuario" required autofocus>
            <button type="submit" class="btn-main">Continuar</button>
        </form>

    <?php elseif ($paso === 'responder'): ?>
        <div class="icon-header"><i class="bi bi-patch-question"></i></div>
        <h2>Pregunta de seguridad</h2>
        <p class="subtitle">Responde para verificar tu identidad</p>

        <?php if ($mensaje): ?>
        <div class="alert-custom alert-<?= $tipo ?>-c">
            <i class="bi bi-exclamation-circle"></i> <?= $mensaje ?>
        </div>
        <?php endif; ?>

        <div class="pregunta-box">
            <i class="bi bi-question-circle me-2 text-primary"></i><?= htmlspecialchars($pregunta) ?>
        </div>

        <form method="POST">
            <input type="hidden" name="accion" value="restablecer">
            <label class="form-label">Tu respuesta</label>
            <input type="text" name="respuesta" class="form-control" required autofocus>

            <label class="form-label">Nueva contraseña</label>
            <input type="password" name="password_nueva" class="form-control" minlength="6" required>

            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="password_confirmar" class="form-control" minlength="6" required>

            <button type="submit" class="btn-main">Restablecer contraseña</button>
        </form>

    <?php else: ?>
        <div class="icon-header" style="background:#f0fdf4;"><i class="bi bi-check-circle" style="color:#16a34a;"></i></div>
        <h2>¡Contraseña actualizada!</h2>
        <p class="subtitle">Ya puedes iniciar sesión con tu nueva contraseña</p>
        <a href="/jj_tienda_deportiva/views/login.php" class="btn-main d-block text-center text-decoration-none" style="line-height:1.8;">
            Ir al inicio de sesión
        </a>
    <?php endif; ?>

    <?php if ($paso !== 'exito'): ?>
    <a href="/jj_tienda_deportiva/views/login.php" class="back-link">
        <i class="bi bi-arrow-left me-1"></i>Volver al inicio de sesión
    </a>
    <?php endif; ?>
</div>
</body>
</html>
