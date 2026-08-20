<?php
require_once __DIR__ . '/../../includes/layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Manual de Ayuda</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Guía rápida de uso del sistema JJ Tienda Deportiva</p>
    </div>
</div>

<div class="row g-4">
    <!-- Menú lateral de secciones -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body p-2">
                <div class="list-group list-group-flush" id="menu-ayuda">
                    <a href="#seccion-productos" class="list-group-item list-group-item-action border-0 rounded-2 mb-1 ayuda-link active">
                        <i class="bi bi-box-seam me-2"></i>Productos
                    </a>
                    <a href="#seccion-ventas" class="list-group-item list-group-item-action border-0 rounded-2 mb-1 ayuda-link">
                        <i class="bi bi-cart-check me-2"></i>Registrar ventas
                    </a>
                    <a href="#seccion-abonos" class="list-group-item list-group-item-action border-0 rounded-2 mb-1 ayuda-link">
                        <i class="bi bi-cash-coin me-2"></i>Abonos pendientes
                    </a>
                    <a href="#seccion-clientes" class="list-group-item list-group-item-action border-0 rounded-2 mb-1 ayuda-link">
                        <i class="bi bi-people me-2"></i>Clientes
                    </a>
                    <a href="#seccion-inventario" class="list-group-item list-group-item-action border-0 rounded-2 mb-1 ayuda-link">
                        <i class="bi bi-clock-history me-2"></i>Historial de inventario
                    </a>
                    <a href="#seccion-reportes" class="list-group-item list-group-item-action border-0 rounded-2 mb-1 ayuda-link">
                        <i class="bi bi-graph-up me-2"></i>Reportes
                    </a>
                    <a href="#seccion-comprobantes" class="list-group-item list-group-item-action border-0 rounded-2 mb-1 ayuda-link">
                        <i class="bi bi-receipt me-2"></i>Comprobantes
                    </a>
                    <a href="#seccion-perfil" class="list-group-item list-group-item-action border-0 rounded-2 mb-1 ayuda-link">
                        <i class="bi bi-person-circle me-2"></i>Mi perfil y contraseña
                    </a>
                    <a href="#seccion-respaldo" class="list-group-item list-group-item-action border-0 rounded-2 ayuda-link">
                        <i class="bi bi-shield-check me-2"></i>Respaldo de datos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="col-lg-9">

        <div id="seccion-productos" class="card mb-4 ayuda-seccion">
            <div class="card-header"><i class="bi bi-box-seam me-2 text-primary"></i>Productos</div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-2" style="font-size:0.9rem;color:#1a2942;">Agregar un producto nuevo</h6>
                <ol style="font-size:0.85rem;color:#475569;line-height:1.9;">
                    <li>Ve al menú <strong>Productos → Agregar Producto</strong>.</li>
                    <li>Escribe el nombre, elige la categoría y la talla (si aplica).</li>
                    <li>Ingresa el precio de venta y la cantidad disponible.</li>
                    <li>Define el <strong>stock mínimo</strong>: cuando la cantidad baje de ese número, el sistema te avisará con una alerta.</li>
                    <li>Haz clic en <strong>Guardar producto</strong>.</li>
                </ol>
                <h6 class="fw-bold mb-2 mt-3" style="font-size:0.9rem;color:#1a2942;">Editar o eliminar</h6>
                <p style="font-size:0.85rem;color:#475569;">
                    En <strong>Productos → Listar Productos</strong>, usa el ícono del lápiz ✏️ para editar
                    o el de la papelera 🗑️ para eliminar un producto.
                </p>
            </div>
        </div>

        <div id="seccion-ventas" class="card mb-4 ayuda-seccion">
            <div class="card-header"><i class="bi bi-cart-check me-2 text-primary"></i>Registrar una venta</div>
            <div class="card-body p-4">
                <ol style="font-size:0.85rem;color:#475569;line-height:1.9;">
                    <li>Ve a <strong>Ventas</strong>.</li>
                    <li>Si el cliente ya está registrado, selecciónalo en la lista. Si no, deja "Sin cliente".</li>
                    <li>Haz clic en <strong>Agregar producto</strong>, escribe el nombre y selecciónalo de la lista.</li>
                    <li>Ingresa la cantidad — el subtotal se calcula solo.</li>
                    <li>Elige el estado del pago:
                        <ul class="mt-1">
                            <li><strong>Pagado</strong>: el cliente paga todo de una vez.</li>
                            <li><strong>Abono</strong>: el cliente paga una parte. Puedes escribir cuánto abona ahora mismo.</li>
                            <li><strong>Pendiente</strong>: el cliente aún no ha pagado nada.</li>
                        </ul>
                    </li>
                    <li>Haz clic en <strong>Registrar venta</strong>. El inventario se descuenta automáticamente.</li>
                </ol>
            </div>
        </div>

        <div id="seccion-abonos" class="card mb-4 ayuda-seccion">
            <div class="card-header"><i class="bi bi-cash-coin me-2 text-primary"></i>Abonos pendientes</div>
            <div class="card-body p-4">
                <p style="font-size:0.85rem;color:#475569;line-height:1.8;">
                    Aquí ves todas las ventas que quedaron con saldo por cobrar. Cuando el cliente
                    te pague una parte más, haz clic en el botón <strong>Abonar</strong> junto a esa
                    venta, escribe el monto que te está pagando y guarda. El sistema descuenta el saldo
                    automáticamente, y si ya queda en $0, la venta pasa a estar "Pagada".
                </p>
            </div>
        </div>

        <div id="seccion-clientes" class="card mb-4 ayuda-seccion">
            <div class="card-header"><i class="bi bi-people me-2 text-primary"></i>Clientes</div>
            <div class="card-body p-4">
                <p style="font-size:0.85rem;color:#475569;line-height:1.8;">
                    En <strong>Clientes</strong> puedes ver, agregar, editar o eliminar la información
                    de tus clientes: nombre, teléfono, correo y dirección. Registrar a tus clientes
                    te permite llevar un historial de sus compras y controlar sus abonos.
                </p>
            </div>
        </div>

        <div id="seccion-inventario" class="card mb-4 ayuda-seccion">
            <div class="card-header"><i class="bi bi-clock-history me-2 text-primary"></i>Historial de inventario</div>
            <div class="card-body p-4">
                <p style="font-size:0.85rem;color:#475569;line-height:1.8;">
                    Aquí queda registrado cada movimiento del inventario: cuando entra mercancía nueva,
                    cuando sale por una venta, o cuando haces un ajuste manual.
                </p>
                <h6 class="fw-bold mb-2 mt-2" style="font-size:0.9rem;color:#1a2942;">Registrar mercancía nueva</h6>
                <p style="font-size:0.85rem;color:#475569;">
                    Haz clic en <strong>Registrar Entrada</strong>, selecciona el producto, escribe
                    cuántas unidades llegaron y por qué (ej: "Compra a proveedor"). El stock se
                    actualiza automáticamente.
                </p>
            </div>
        </div>

        <div id="seccion-reportes" class="card mb-4 ayuda-seccion">
            <div class="card-header"><i class="bi bi-graph-up me-2 text-primary"></i>Reportes</div>
            <div class="card-body p-4">
                <p style="font-size:0.85rem;color:#475569;line-height:1.8;">
                    Selecciona un rango de fechas (desde – hasta) y haz clic en <strong>Generar</strong>
                    para ver todas las ventas de ese período, con el total de ingresos. Puedes descargar
                    esa información en un archivo de Excel con el botón <strong>Excel</strong>.
                </p>
            </div>
        </div>

        <div id="seccion-comprobantes" class="card mb-4 ayuda-seccion">
            <div class="card-header"><i class="bi bi-receipt me-2 text-primary"></i>Comprobantes de venta</div>
            <div class="card-body p-4">
                <p style="font-size:0.85rem;color:#475569;line-height:1.8;">
                    Cada venta genera un comprobante que puedes imprimir o guardar como PDF para
                    dárselo al cliente. Lo encuentras justo después de registrar la venta, o en
                    <strong>Historial de Ventas</strong> haciendo clic en el ícono del recibo 🧾.
                </p>
            </div>
        </div>

        <div id="seccion-perfil" class="card mb-4 ayuda-seccion">
            <div class="card-header"><i class="bi bi-person-circle me-2 text-primary"></i>Mi perfil y contraseña</div>
            <div class="card-body p-4">
                <p style="font-size:0.85rem;color:#475569;line-height:1.8;">
                    Haz clic en tu nombre (arriba a la derecha) → <strong>Mi perfil</strong>. Ahí puedes
                    cambiar tu nombre, tu foto y tu contraseña.
                </p>
                <div class="alert alert-info d-flex align-items-start gap-2" style="border-radius:10px;font-size:0.82rem;">
                    <i class="bi bi-lightbulb mt-1"></i>
                    <span>Configura tu <strong>pregunta de seguridad</strong> en el perfil — así, si olvidas
                    tu contraseña, puedes recuperarla tú misma desde la pantalla de inicio de sesión,
                    sin depender de nadie más.</span>
                </div>
            </div>
        </div>

        <div id="seccion-respaldo" class="card ayuda-seccion">
            <div class="card-header"><i class="bi bi-shield-check me-2 text-primary"></i>Respaldo de datos</div>
            <div class="card-body p-4">
                <p style="font-size:0.85rem;color:#475569;line-height:1.8;">
                    En <strong>Respaldo de Datos</strong> puedes descargar una copia completa de toda
                    la información del sistema. Es recomendable hacerlo de vez en cuando y guardar
                    el archivo en un USB o en tu correo, por si el computador llega a fallar.
                </p>
            </div>
        </div>

    </div>
</div>

<script>
document.querySelectorAll('.ayuda-link').forEach(link => {
    link.addEventListener('click', function(e) {
        document.querySelectorAll('.ayuda-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>

<style>
.ayuda-link { color: #475569; font-size: 0.85rem; transition: all 0.15s; }
.ayuda-link:hover { background: #f0f2f5; color: #1a2942; }
.ayuda-link.active { background: #eff6ff; color: #2563eb; font-weight: 600; }
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
