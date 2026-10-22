<?php
// includes/auth.php
// Autenticación y control de acceso por roles (admin / vendedor).
// Se incluye al inicio de cada pantalla ANTES de procesar formularios o consultar la base de datos.
//
//   requerirLogin()              -> exige sesión iniciada (cualquier rol)
//   requerirRol(['admin'])       -> exige sesión iniciada y que el rol esté en la lista
//   esAdmin()                    -> true si el usuario actual es administrador (para mostrar/ocultar botones)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// BASE_URL: carpeta donde está instalado el sistema, calculada a partir de la URL actual.
//   Servidor local (XAMPP):  /jj_tienda_deportiva
//   InfinityFree (raíz):     (vacío)
// Así los enlaces y redirecciones funcionan igual en ambos entornos.
if (!defined('BASE_URL')) {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base = '';
    foreach (['/views/', '/controllers/', '/includes/'] as $marca) {
        $pos = strpos($script, $marca);
        if ($pos !== false) {
            $base = substr($script, 0, $pos);
            break;
        }
    }
    define('BASE_URL', rtrim($base, '/'));
}

function usuarioAutenticado() {
    return !empty($_SESSION['usuario']);
}

function rolActual() {
    return $_SESSION['rol'] ?? '';
}

function esAdmin() {
    return rolActual() === 'admin';
}

// Pantalla de inicio según el rol: el administrador ve el Dashboard; el vendedor entra a Ventas.
function urlInicioPorRol() {
    return BASE_URL . (esAdmin() ? '/views/dashboard.php' : '/views/ventas/registrar_venta.php');
}

function requerirLogin() {
    // Sin sesión, o sesión abierta antes de activar los roles (no trae rol): volver a iniciar sesión.
    if (!usuarioAutenticado() || rolActual() === '') {
        $_SESSION = [];
        header('Location: ' . BASE_URL . '/views/login.php');
        exit;
    }
}

function requerirRol(array $roles) {
    requerirLogin();
    if (!in_array(rolActual(), $roles, true)) {
        $_SESSION['flash_error'] = 'No tienes permiso para acceder a esa sección.';
        header('Location: ' . urlInicioPorRol());
        exit;
    }
}

// Token anti-CSRF para formularios que modifican datos sensibles (gestión de usuarios).
function csrfToken() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfValido($token) {
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}
