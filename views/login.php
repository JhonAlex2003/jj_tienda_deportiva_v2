<?php
// views/login.php
session_start();
require_once __DIR__ . '/../controllers/usuariosController.php';

$error = '';
$controlador = new UsuariosController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $usuarioData = $controlador->login($usuario, $contrasena);
    if ($usuarioData) {
        $_SESSION['usuario'] = $usuarioData['usuario'];
        $_SESSION['id_usuario'] = $usuarioData['id_usuario'];
        $_SESSION['foto_perfil'] = $usuarioData['foto_perfil'] ?? null;
        header('Location: ../views/dashboard.php');
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — JJ Tienda Deportiva</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    display: flex;
    background: #f0f2f5;
}

/* Panel izquierdo — decorativo */
.login-left {
    flex: 1;
    background: linear-gradient(145deg, #1a2942 0%, #2563eb 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    position: relative;
    overflow: hidden;
}

.login-left::before {
    content: '';
    position: absolute;
    width: 400px; height: 400px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
    top: -100px; right: -100px;
}

.login-left::after {
    content: '';
    position: absolute;
    width: 300px; height: 300px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
    bottom: -80px; left: -80px;
}

.login-left-content {
    position: relative;
    z-index: 1;
    text-align: center;
    color: white;
    max-width: 400px;
}

.login-left-content img {
    width: 90px;
    height: 90px;
    border-radius: 20px;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.2);
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.login-left-content h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    color: white;
}

.login-left-content p {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.65);
    line-height: 1.6;
    margin-bottom: 2rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.65rem 1rem;
    background: rgba(255,255,255,0.07);
    border-radius: 10px;
    margin-bottom: 0.6rem;
    text-align: left;
}

.feature-item i {
    font-size: 1.1rem;
    color: #60a5fa;
    flex-shrink: 0;
}

.feature-item span {
    font-size: 0.825rem;
    color: rgba(255,255,255,0.8);
}

/* Panel derecho — formulario */
.login-right {
    width: 440px;
    background: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2.5rem;
    box-shadow: -4px 0 25px rgba(0,0,0,0.06);
}

.login-form-header {
    text-align: center;
    margin-bottom: 2rem;
    width: 100%;
}

.login-form-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a2942;
    margin-bottom: 0.4rem;
}

.login-form-header p {
    font-size: 0.825rem;
    color: #94a3b8;
}

.form-group {
    width: 100%;
    margin-bottom: 1rem;
}

.form-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.4rem;
    display: block;
}

.input-wrapper {
    position: relative;
}

.input-wrapper i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.95rem;
}

.input-wrapper input {
    width: 100%;
    padding: 0.65rem 0.85rem 0.65rem 2.4rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.875rem;
    font-family: 'Inter', sans-serif;
    color: #2d3748;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
    background: #f8fafc;
}

.input-wrapper input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    background: #fff;
}

.btn-login {
    width: 100%;
    padding: 0.75rem;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 0.5rem;
    box-shadow: 0 4px 12px rgba(37,99,235,0.3);
}

.btn-login:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    box-shadow: 0 6px 18px rgba(37,99,235,0.4);
    transform: translateY(-1px);
}

.btn-login:active {
    transform: translateY(0);
}

.error-msg {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    border-radius: 8px;
    padding: 0.65rem 0.85rem;
    font-size: 0.825rem;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 1rem;
    width: 100%;
}

.login-footer {
    margin-top: 2rem;
    text-align: center;
    font-size: 0.75rem;
    color: #cbd5e1;
}

@media (max-width: 768px) {
    .login-left { display: none; }
    .login-right { width: 100%; padding: 2rem 1.5rem; }
}
</style>
</head>
<body>

<!-- PANEL IZQUIERDO -->
<div class="login-left">
    <div class="login-left-content">
        <img src="/jj_tienda_deportiva/assets/assets_img/logo.png" alt="Logo JJ Tienda">
        <h1>JJ Tienda Deportiva</h1>
        <p>Sistema de gestión de inventario, ventas y clientes para optimizar la operación del negocio.</p>

        <div class="feature-item">
            <i class="bi bi-box-seam"></i>
            <span>Control de inventario en tiempo real</span>
        </div>
        <div class="feature-item">
            <i class="bi bi-cart-check"></i>
            <span>Registro de ventas y punto de venta</span>
        </div>
        <div class="feature-item">
            <i class="bi bi-people"></i>
            <span>Gestión de clientes y abonos</span>
        </div>
        <div class="feature-item">
            <i class="bi bi-graph-up"></i>
            <span>Reportes y exportación a Excel</span>
        </div>
    </div>
</div>

<!-- PANEL DERECHO -->
<div class="login-right">
    <div class="login-form-header">
        <h2>Bienvenido</h2>
        <p>Ingresa tus credenciales para acceder al sistema</p>
    </div>

    <?php if($error): ?>
    <div class="error-msg">
        <i class="bi bi-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" style="width:100%">
        <div class="form-group">
            <label class="form-label">Usuario</label>
            <div class="input-wrapper">
                <i class="bi bi-person"></i>
                <input type="text" name="usuario" placeholder="Ingresa tu usuario" required autofocus>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Contraseña</label>
            <div class="input-wrapper">
                <i class="bi bi-lock"></i>
                <input type="password" name="contrasena" placeholder="Ingresa tu contraseña" required>
            </div>
        </div>
        <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar al sistema
        </button>
    </form>

    <a href="/jj_tienda_deportiva/views/recuperar_password.php"
       style="display:block;text-align:center;margin-top:1rem;font-size:0.8rem;color:#64748b;text-decoration:none;">
        ¿Olvidaste tu contraseña?
    </a>

    <div class="login-footer">
        © 2026 JJ Tienda Deportiva · Proyecto de Grado UNAD
    </div>
</div>

</body>
</html>
