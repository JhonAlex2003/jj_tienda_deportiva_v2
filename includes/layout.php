<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario'])) {
    header("Location: /jj_tienda_deportiva/login.php");
    exit();
}

// Alertas de stock bajo (para la campana de notificaciones)
require_once __DIR__ . '/../config/db.php';
$dbAlertas = new Database();
$connAlertas = $dbAlertas->connect();
$resultAlertas = $connAlertas->query("
    SELECT id_producto, nombre, talla, cantidad, stock_minimo
    FROM productos
    WHERE cantidad <= stock_minimo AND estado = 1
    ORDER BY cantidad ASC
    LIMIT 8
");
$productosAlerta = [];
if ($resultAlertas) {
    while ($row = $resultAlertas->fetch_assoc()) {
        $productosAlerta[] = $row;
    }
}
$totalAlertas = count($productosAlerta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JJ Tienda Deportiva</title>

<!-- BOOTSTRAP -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- ICONOS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<!-- GOOGLE FONTS -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- CUSTOM CSS -->
<link rel="stylesheet" href="/jj_tienda_deportiva/assets/css/style.css">

<style>
/* =====================
   RESET & BASE
   ===================== */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Inter', sans-serif;
    font-size: 0.9rem;
    background-color: #f0f2f5;
    color: #2d3748;
    display: flex;
    min-height: 100vh;
}

/* =====================
   SIDEBAR
   ===================== */
.sidebar {
    width: 255px;
    min-height: 100vh;
    background: #1a2942;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0;
    z-index: 100;
    box-shadow: 4px 0 15px rgba(0,0,0,0.15);
}

.sidebar-brand {
    padding: 1.5rem 1.2rem;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    gap: 12px;
}

.sidebar-brand img {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.15);
}

.sidebar-brand-text {
    display: flex;
    flex-direction: column;
}

.sidebar-brand-text span:first-child {
    font-size: 0.95rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.3px;
}

.sidebar-brand-text span:last-child {
    font-size: 0.72rem;
    color: rgba(255,255,255,0.45);
    margin-top: 1px;
}

.sidebar-nav {
    padding: 1rem 0.75rem;
    flex: 1;
    overflow-y: auto;
}

.sidebar-section-label {
    font-size: 0.65rem;
    font-weight: 600;
    color: rgba(255,255,255,0.3);
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 0.6rem 0.5rem 0.3rem;
    margin-top: 0.5rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.6rem 0.85rem;
    border-radius: 8px;
    color: rgba(255,255,255,0.65);
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    margin-bottom: 2px;
    text-decoration: none;
}

.nav-link:hover {
    background: rgba(255,255,255,0.08);
    color: #fff;
}

.nav-link.active {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    box-shadow: 0 4px 12px rgba(37,99,235,0.35);
}

.nav-link i {
    font-size: 1rem;
    width: 18px;
    text-align: center;
    flex-shrink: 0;
}

.nav-link .chevron {
    margin-left: auto;
    font-size: 0.7rem;
    transition: transform 0.2s;
}

.nav-link[aria-expanded="true"] .chevron {
    transform: rotate(180deg);
}

.submenu {
    list-style: none;
    padding: 0.2rem 0 0.2rem 1rem;
}

.submenu li a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0.45rem 0.85rem;
    border-radius: 6px;
    color: rgba(255,255,255,0.5);
    font-size: 0.825rem;
    text-decoration: none;
    transition: all 0.2s;
}

.submenu li a:hover {
    color: #fff;
    background: rgba(255,255,255,0.06);
}

.submenu li a.active {
    color: #60a5fa;
}

.sidebar-footer {
    padding: 1rem 1.2rem;
    border-top: 1px solid rgba(255,255,255,0.08);
}

.sidebar-footer a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: rgba(255,255,255,0.5);
    font-size: 0.825rem;
    text-decoration: none;
    padding: 0.5rem 0.85rem;
    border-radius: 8px;
    transition: all 0.2s;
}

.sidebar-footer a:hover {
    background: rgba(255,59,59,0.15);
    color: #fc8181;
}

/* =====================
   MAIN CONTENT
   ===================== */
.main-content {
    margin-left: 255px;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* =====================
   TOPBAR
   ===================== */
.topbar {
    background: #fff;
    padding: 0 1.75rem;
    height: 62px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 99;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

.topbar-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1a2942;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.topbar-user {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 0.4rem 0.75rem;
    border-radius: 10px;
    transition: background 0.2s;
    text-decoration: none;
}

.topbar-user:hover {
    background: #f0f2f5;
}

.topbar-user img {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}

.topbar-user-info span:first-child {
    display: block;
    font-weight: 600;
    font-size: 0.825rem;
    color: #1a2942;
}

.topbar-user-info span:last-child {
    display: block;
    font-size: 0.72rem;
    color: #94a3b8;
}

/* =====================
   PAGE CONTENT
   ===================== */
.page-content {
    padding: 1.75rem;
    flex: 1;
}

/* =====================
   CARDS
   ===================== */
.card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    transition: box-shadow 0.2s, transform 0.2s;
    color: #2d3748;
}

.card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.card-header {
    background: transparent;
    border-bottom: 1px solid #f1f5f9;
    padding: 1rem 1.25rem 0.75rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: #1a2942;
}

/* =====================
   STAT CARDS
   ===================== */
.stat-card {
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: none;
    color: #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.stat-card .stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}

.stat-card .stat-label {
    font-size: 0.775rem;
    font-weight: 500;
    opacity: 0.85;
    margin-bottom: 3px;
}

.stat-card .stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1;
}

.stat-blue   { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
.stat-red    { background: linear-gradient(135deg, #ef4444, #dc2626); }
.stat-green  { background: linear-gradient(135deg, #10b981, #059669); }
.stat-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }

/* =====================
   TABLAS
   ===================== */
.table {
    color: #2d3748;
    font-size: 0.875rem;
}

.table thead th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 0.775rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
    padding: 0.75rem 1rem;
}

.table tbody td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table tbody tr:hover {
    background: #f8fafc;
}

/* =====================
   BOTONES
   ===================== */
.btn {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 0.825rem;
    border-radius: 8px;
    padding: 0.45rem 1rem;
    transition: all 0.2s;
}

.btn-primary {
    background: #2563eb;
    border-color: #2563eb;
}

.btn-primary:hover {
    background: #1d4ed8;
    border-color: #1d4ed8;
    box-shadow: 0 4px 12px rgba(37,99,235,0.3);
}

/* =====================
   BADGES
   ===================== */
.badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.35em 0.65em;
    border-radius: 6px;
}

/* =====================
   FORMS
   ===================== */
.form-control, .form-select {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #2d3748;
    padding: 0.5rem 0.85rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus, .form-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

.form-label {
    font-weight: 500;
    font-size: 0.825rem;
    color: #475569;
    margin-bottom: 0.4rem;
}

/* =====================
   CANVAS (CHARTS)
   ===================== */
canvas {
    border-radius: 8px;
}

/* =====================
   DROPDOWN
   ===================== */
.dropdown-menu {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    padding: 0.5rem;
}

.dropdown-item {
    border-radius: 6px;
    font-size: 0.825rem;
    padding: 0.5rem 0.85rem;
    color: #2d3748;
    transition: background 0.15s;
}

.dropdown-item:hover {
    background: #f0f2f5;
    color: #1a2942;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="/jj_tienda_deportiva/assets/assets_img/logo.png" alt="Logo">
        <div class="sidebar-brand-text">
            <span>JJ Tienda</span>
            <span>Sistema de Gestión</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Principal</div>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"
           href="/jj_tienda_deportiva/views/dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="sidebar-section-label">Inventario</div>
        <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), [
            'listar_productos.php','agregar_producto.php','actualizar_producto.php',
            'listar_categorias.php','agregar_categoria.php','actualizar_categoria.php'
        ]) ? 'active' : '' ?>"
           href="#productosSubmenu" data-bs-toggle="collapse"
           aria-expanded="<?= in_array(basename($_SERVER['PHP_SELF']), [
            'listar_productos.php','agregar_producto.php','actualizar_producto.php',
            'listar_categorias.php','agregar_categoria.php','actualizar_categoria.php'
           ]) ? 'true' : 'false' ?>">
            <i class="bi bi-box-seam"></i> Productos
            <i class="bi bi-chevron-down chevron"></i>
        </a>
        <ul class="submenu collapse <?= in_array(basename($_SERVER['PHP_SELF']), [
            'listar_productos.php','agregar_producto.php','actualizar_producto.php',
            'listar_categorias.php','agregar_categoria.php','actualizar_categoria.php'
        ]) ? 'show' : '' ?>" id="productosSubmenu">
            <li><a href="/jj_tienda_deportiva/views/productos/listar_productos.php"
                   class="<?= basename($_SERVER['PHP_SELF']) == 'listar_productos.php' ? 'active' : '' ?>">
                <i class="bi bi-list-ul"></i> Listar Productos</a></li>
            <li><a href="/jj_tienda_deportiva/views/productos/agregar_producto.php"
                   class="<?= basename($_SERVER['PHP_SELF']) == 'agregar_producto.php' ? 'active' : '' ?>">
                <i class="bi bi-plus-circle"></i> Agregar Producto</a></li>
            <li><a href="/jj_tienda_deportiva/views/categorias/listar_categorias.php"
                   class="<?= basename($_SERVER['PHP_SELF']) == 'listar_categorias.php' ? 'active' : '' ?>">
                <i class="bi bi-tags"></i> Categorías</a></li>
        </ul>

        <div class="sidebar-section-label">Movimientos</div>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'historial_movimientos.php' ? 'active' : '' ?>"
           href="/jj_tienda_deportiva/views/inventario/historial_movimientos.php">
            <i class="bi bi-clock-history"></i> Historial de Inventario
        </a>
        <div class="sidebar-section-label">Operaciones</div>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'listar_clientes.php' ? 'active' : '' ?>"
           href="/jj_tienda_deportiva/views/clientes/listar_clientes.php">
            <i class="bi bi-people"></i> Clientes
        </a>

        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'registrar_venta.php' ? 'active' : '' ?>"
           href="/jj_tienda_deportiva/views/ventas/registrar_venta.php">
            <i class="bi bi-cart-check"></i> Ventas
        </a>

        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'abonos_pendientes.php' ? 'active' : '' ?>"
           href="/jj_tienda_deportiva/views/ventas/abonos_pendientes.php">
            <i class="bi bi-cash-coin"></i> Abonos Pendientes
        </a>

        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'historial_ventas.php' ? 'active' : '' ?>"
           href="/jj_tienda_deportiva/views/ventas/historial_ventas.php">
            <i class="bi bi-clock-history"></i> Historial de Ventas
        </a>

        <div class="sidebar-section-label">Análisis</div>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reportes.php' ? 'active' : '' ?>"
           href="/jj_tienda_deportiva/views/reportes/reportes.php">
            <i class="bi bi-graph-up"></i> Reportes
        </a>

        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'backup.php' ? 'active' : '' ?>"
           href="/jj_tienda_deportiva/views/backup/backup.php">
            <i class="bi bi-shield-check"></i> Respaldo de Datos
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="/jj_tienda_deportiva/views/logout.php">
            <i class="bi bi-box-arrow-left"></i> Cerrar sesión
        </a>
    </div>
</aside>

<!-- MAIN -->
<div class="main-content">
    <!-- TOPBAR -->
    <div class="topbar">
        <span class="topbar-title">JJ Tienda Deportiva &mdash; Sistema de Gestión</span>
        <div class="topbar-right">
            <div class="dropdown me-2">
                <a href="#" class="position-relative d-flex align-items-center justify-content-center dropdown-toggle"
                   data-bs-toggle="dropdown" style="width:38px;height:38px;border-radius:10px;text-decoration:none;
                   transition:background 0.2s;" onmouseover="this.style.background='#f0f2f5'" onmouseout="this.style.background='transparent'">
                    <i class="bi bi-bell" style="font-size:1.15rem;color:#475569;"></i>
                    <?php if ($totalAlertas > 0): ?>
                    <span class="position-absolute badge rounded-pill bg-danger"
                          style="top:2px;right:0px;font-size:0.6rem;padding:3px 5px;">
                        <?= $totalAlertas ?>
                    </span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-0" style="width:320px;max-height:400px;overflow-y:auto;">
                    <li class="px-3 py-2 border-bottom">
                        <span class="fw-bold" style="font-size:0.875rem;color:#1a2942;">
                            <i class="bi bi-exclamation-triangle text-danger me-1"></i>Alertas de inventario
                        </span>
                    </li>
                    <?php if ($totalAlertas > 0): ?>
                        <?php foreach ($productosAlerta as $pa): ?>
                        <li>
                            <a class="dropdown-item d-flex justify-content-between align-items-center py-2"
                               href="/jj_tienda_deportiva/views/productos/listar_productos.php">
                                <span style="font-size:0.8rem;">
                                    <?= htmlspecialchars($pa['nombre']) ?>
                                    <?= $pa['talla'] ? ' ('.$pa['talla'].')' : '' ?>
                                </span>
                                <span class="badge bg-danger"><?= $pa['cantidad'] ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li>
                            <a class="dropdown-item text-center text-primary py-2" style="font-size:0.8rem;"
                               href="/jj_tienda_deportiva/views/inventario/historial_movimientos.php">
                                Ver historial de inventario
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="px-3 py-4 text-center text-muted" style="font-size:0.825rem;">
                            <i class="bi bi-check-circle text-success d-block mb-1" style="font-size:1.5rem;"></i>
                            Todo el inventario está en orden
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="dropdown">
                <a href="#" class="topbar-user dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?= !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/jj_tienda_deportiva/assets/assets_img/user.png' ?>" alt="Usuario">
                    <div class="topbar-user-info">
                        <span><?= $_SESSION['usuario'] ?></span>
                        <span>Administrador</span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/jj_tienda_deportiva/views/perfil/perfil.php">
                        <i class="bi bi-person-circle me-2"></i>Mi perfil
                    </a></li>
                    <li><a class="dropdown-item" href="/jj_tienda_deportiva/views/ayuda/manual_ayuda.php">
                        <i class="bi bi-question-circle me-2"></i>Manual de ayuda
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/jj_tienda_deportiva/views/logout.php">
                        <i class="bi bi-box-arrow-left me-2"></i>Cerrar sesión
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- PAGE CONTENT -->
    <div class="page-content">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
