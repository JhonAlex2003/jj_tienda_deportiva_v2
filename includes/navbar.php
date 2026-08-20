<?php
// includes/navbar.php
// Asegúrate de llamar session_start() antes de incluir este archivo (se hace en layout.php)
$base = '/jj_tienda_deportiva'; // ruta base del proyecto en localhost (ajusta el nombre si tu carpeta se llama diferente)
?>
<!-- SIDEBAR -->
<div class="bg-dark text-white p-3" style="width: 250px; min-height: 100vh;">
    <h4 class="text-center mb-4">J-Systems</h4>

    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="<?= $base ?>/views/dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="<?= $base ?>/views/productos/listar_productos.php">
                <i class="bi bi-box-seam me-2"></i> Productos
            </a>
        </li>

        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="<?= $base ?>/views/clientes/listar_clientes.php">
                <i class="bi bi-people me-2"></i> Clientes
            </a>
        </li>

        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="<?= $base ?>/views/ventas/registrar_venta.php">
                <i class="bi bi-cart-check me-2"></i> Ventas
            </a>
        </li>

        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="<?= $base ?>/views/reportes/reportes.php">
                <i class="bi bi-graph-up me-2"></i> Reportes
            </a>
        </li>
    </ul>

    <hr style="border-color: rgba(255,255,255,0.05)">

    <div class="mt-4 text-center small">
        <div class="fw-bold"><?= isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Invitado' ?></div>
        <div class="text-muted">Super Admin</div>
        <a href="<?= $base ?>/views/logout.php" class="btn btn-sm btn-outline-light mt-2">Cerrar sesión</a>
    </div>
</div>
