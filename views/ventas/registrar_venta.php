<?php
require_once __DIR__ . "/../../includes/layout.php";
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ventasController.php';
require_once __DIR__ . '/../../controllers/clientesController.php';
require_once __DIR__ . '/../../controllers/ProductosController.php';

$db = new Database();
$conn = $db->connect();

$ventasController    = new VentasController($conn);
$clientesController  = new ClientesController($conn);
$productosController = new ProductosController($conn);

$clientes  = $clientesController->listar();
$productos = $productosController->listar();

$mensaje = '';
$tipo    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente     = !empty($_POST['id_cliente']) ? $_POST['id_cliente'] : null;
    $estado_pago    = $_POST['estado_pago'] ?? 'pagado';
    $productosVenta = $_POST['productos'] ?? [];
    $monto_abono    = !empty($_POST['monto_abono']) ? (float)$_POST['monto_abono'] : 0;

    $filas_validas = [];
    foreach ($productosVenta as $fila) {
        if (!empty($fila['id_producto']) && !empty($fila['cantidad'])) {
            $filas_validas[] = $fila;
        }
    }

    if (count($filas_validas) === 0) {
        $mensaje = "Debes agregar al menos un producto con cantidad válida.";
        $tipo    = 'danger';
    } else {
        $id_venta = $ventasController->registrarVenta($filas_validas, $id_cliente, $estado_pago, $monto_abono);
        if ($id_venta) {
            $mensaje = "Venta registrada exitosamente. ID de venta: #$id_venta";
            if ($estado_pago === 'abono' && $monto_abono > 0) {
                $mensaje .= " — Abono inicial de $" . number_format($monto_abono, 0, ',', '.') . " registrado.";
            }
            $tipo    = 'success';
            $ultima_venta_id = $id_venta;
        } else {
            $mensaje = "Error al registrar la venta. Intenta nuevamente.";
            $tipo    = 'danger';
        }
    }
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1a2942;">Registrar Venta</h5>
        <p class="text-muted mb-0" style="font-size:0.825rem;">Punto de venta — registra una nueva transacción</p>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
<div class="alert alert-<?= $tipo ?> d-flex align-items-center justify-content-between gap-2 mb-4" style="border-radius:10px;">
    <span class="d-flex align-items-center gap-2">
        <i class="bi bi-<?= $tipo === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= $mensaje ?>
    </span>
    <?php if (!empty($ultima_venta_id)): ?>
    <a href="comprobante_venta.php?id=<?= $ultima_venta_id ?>" target="_blank" class="btn btn-sm btn-success">
        <i class="bi bi-receipt me-1"></i>Ver comprobante
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<form method="POST" autocomplete="off">
    <div class="row g-4">

        <!-- Panel izquierdo: productos -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-cart me-2 text-primary"></i>Productos de la venta</span>
                    <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1" id="add-product">
                        <i class="bi bi-plus-circle"></i> Agregar producto
                    </button>
                </div>
                <div class="card-body p-3">

                    <!-- Cabecera tabla -->
                    <div class="row g-2 mb-2 px-1">
                        <div class="col-6">
                            <span class="text-muted" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Producto</span>
                        </div>
                        <div class="col-2 text-center">
                            <span class="text-muted" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Precio</span>
                        </div>
                        <div class="col-2 text-center">
                            <span class="text-muted" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Cant.</span>
                        </div>
                        <div class="col-2 text-center">
                            <span class="text-muted" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Subtotal</span>
                        </div>
                    </div>

                    <div id="productos-container"></div>

                    <!-- Fila inicial -->
                    <template id="producto-template">
                        <div class="producto-row row g-2 align-items-start mb-2 pb-2 border-bottom">
                            <div class="col-6">
                                <div class="position-relative">
                                    <input type="text" class="form-control producto-search"
                                           placeholder="Buscar producto..." style="font-size:0.85rem;">
                                    <div class="suggestions-wrapper"></div>
                                    <input type="hidden" class="producto-id">
                                </div>
                            </div>
                            <div class="col-2">
                                <input type="text" class="form-control producto-precio text-center"
                                       readonly placeholder="$0" style="font-size:0.85rem;background:#f8fafc;">
                                <input type="hidden" class="producto-precio-raw">
                            </div>
                            <div class="col-2">
                                <input type="number" class="form-control producto-cantidad text-center"
                                       min="1" placeholder="0" style="font-size:0.85rem;">
                            </div>
                            <div class="col-1 d-flex align-items-center justify-content-center">
                                <span class="producto-subtotal fw-600 text-primary" style="font-size:0.85rem;font-weight:600;">$0</span>
                            </div>
                            <div class="col-1 d-flex align-items-center justify-content-center">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-product" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                </div>
                <div class="card-body border-top pt-3 pb-3 d-flex justify-content-end align-items-center gap-3">
                    <span class="text-muted" style="font-size:0.875rem;">Total de la venta:</span>
                    <span id="total-venta" class="fw-bold" style="font-size:1.4rem;color:#1a2942;">$0</span>
                </div>
            </div>
        </div>

        <!-- Panel derecho: cliente y pago -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-person me-2 text-primary"></i>Cliente
                </div>
                <div class="card-body p-3">
                    <label class="form-label">Seleccionar cliente <span class="text-muted">(opcional)</span></label>
                    <select name="id_cliente" class="form-select" style="font-size:0.875rem;">
                        <option value="">— Venta sin cliente —</option>
                        <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted mt-1 d-block">Si el cliente no está registrado puedes dejarlo en blanco.</small>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-credit-card me-2 text-primary"></i>Estado del pago
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-2">
                        <div class="form-check p-3 border rounded-3 <?= true ? 'border-success bg-success bg-opacity-10' : '' ?>" style="cursor:pointer;" onclick="selectPago(this,'pagado')">
                            <input class="form-check-input" type="radio" name="estado_pago" value="pagado" id="pago1" checked>
                            <label class="form-check-label d-flex align-items-center gap-2" for="pago1" style="cursor:pointer;">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <div>
                                    <div style="font-size:0.875rem;font-weight:600;">Pagado</div>
                                    <div style="font-size:0.75rem;color:#64748b;">Pago completo recibido</div>
                                </div>
                            </label>
                        </div>
                        <div class="form-check p-3 border rounded-3" style="cursor:pointer;" onclick="selectPago(this,'abono')">
                            <input class="form-check-input" type="radio" name="estado_pago" value="abono" id="pago2">
                            <label class="form-check-label d-flex align-items-center gap-2" for="pago2" style="cursor:pointer;">
                                <i class="bi bi-clock-fill text-warning"></i>
                                <div>
                                    <div style="font-size:0.875rem;font-weight:600;">Abono</div>
                                    <div style="font-size:0.75rem;color:#64748b;">Pago parcial — queda deuda</div>
                                </div>
                            </label>
                        </div>

                        <div id="campo-monto-abono" class="d-none ps-3 pe-1">
                            <label class="form-label" style="font-size:0.8rem;">Monto que abona ahora</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto_abono" id="input-monto-abono"
                                       class="form-control" min="1" placeholder="0" style="font-size:0.875rem;">
                            </div>
                            <small class="text-muted" id="texto-saldo-restante"></small>
                        </div>

                        <div class="form-check p-3 border rounded-3" style="cursor:pointer;" onclick="selectPago(this,'pendiente')">
                            <input class="form-check-input" type="radio" name="estado_pago" value="pendiente" id="pago3">
                            <label class="form-check-label d-flex align-items-center gap-2" for="pago3" style="cursor:pointer;">
                                <i class="bi bi-x-circle-fill text-danger"></i>
                                <div>
                                    <div style="font-size:0.875rem;font-weight:600;">Pendiente</div>
                                    <div style="font-size:0.75rem;color:#64748b;">Sin pago recibido aún</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3" style="font-size:1rem;font-weight:600;">
                <i class="bi bi-check-circle me-2"></i>Registrar venta
            </button>
        </div>
    </div>
</form>

<!-- Datos productos para JS -->
<script>
const productosData = [
    <?php foreach ($productos as $p): ?>
    { id: <?= $p['id_producto'] ?>, nombre: "<?= addslashes($p['nombre']) ?>", precio: <?= $p['precio'] ?>, stock: <?= $p['cantidad'] ?> },
    <?php endforeach; ?>
];
</script>

<style>
.suggestions-list {
    position: absolute;
    background: #fff;
    border: 1px solid #e2e8f0;
    max-height: 200px;
    overflow-y: auto;
    z-index: 9999;
    width: 100%;
    border-radius: 8px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    top: 100%;
    left: 0;
}
.suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    font-size: 0.825rem;
    color: #2d3748;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.suggestion-item:hover { background: #f0f2f5; }
.suggestion-item .stock-badge {
    font-size: 0.7rem;
    padding: 2px 6px;
    border-radius: 4px;
    background: #dcfce7;
    color: #16a34a;
}
.form-check-input:checked { background-color: #2563eb; border-color: #2563eb; }
</style>

<script>
let rowIndex = 0;
const container = document.getElementById('productos-container');
const template  = document.getElementById('producto-template');

function formatCurrency(val) {
    return '$' + Number(val).toLocaleString('es-CO');
}

function actualizarTotal() {
    let total = 0;
    document.querySelectorAll('.producto-subtotal').forEach(el => {
        total += parseFloat(el.dataset.value || 0);
    });
    document.getElementById('total-venta').textContent = formatCurrency(total);
}

function activarFila(row, idx) {
    const search    = row.querySelector('.producto-search');
    const idHidden  = row.querySelector('.producto-id');
    const precio    = row.querySelector('.producto-precio');
    const precioRaw = row.querySelector('.producto-precio-raw');
    const cantidad  = row.querySelector('.producto-cantidad');
    const subtotal  = row.querySelector('.producto-subtotal');
    const wrapper   = row.querySelector('.suggestions-wrapper');

    // Asignar names correctos
    idHidden.name  = `productos[${idx}][id_producto]`;
    cantidad.name  = `productos[${idx}][cantidad]`;
    precioRaw.name = `productos[${idx}][precio_unitario]`;

    search.addEventListener('input', () => {
        const txt = search.value.toLowerCase();
        wrapper.innerHTML = '';
        if (!txt) return;
        const filtro = productosData.filter(p => p.nombre.toLowerCase().includes(txt));
        if (!filtro.length) return;
        const list = document.createElement('div');
        list.className = 'suggestions-list';
        filtro.forEach(p => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.innerHTML = `<span>${p.nombre}</span><span class="stock-badge">Stock: ${p.stock}</span>`;
            item.onclick = () => {
                search.value    = p.nombre;
                idHidden.value  = p.id;
                precio.value    = formatCurrency(p.precio);
                precioRaw.value = p.precio;
                wrapper.innerHTML = '';
                recalcular();
            };
            list.appendChild(item);
        });
        wrapper.appendChild(list);
    });

    function recalcular() {
        const cant = parseInt(cantidad.value) || 0;
        const prec = parseFloat(precioRaw.value) || 0;
        const sub  = cant * prec;
        subtotal.textContent   = formatCurrency(sub);
        subtotal.dataset.value = sub;
        actualizarTotal();
    }

    cantidad.addEventListener('input', recalcular);

    row.querySelector('.remove-product').addEventListener('click', () => {
        row.remove();
        actualizarTotal();
    });

    document.addEventListener('click', e => {
        if (!row.contains(e.target)) wrapper.innerHTML = '';
    });
}

function agregarFila() {
    const clone = template.content.cloneNode(true);
    const row   = clone.querySelector('.producto-row');
    container.appendChild(clone);
    activarFila(container.lastElementChild, rowIndex++);
}

document.getElementById('add-product').addEventListener('click', agregarFila);

function selectPago(el, val) {
    document.querySelectorAll('.form-check').forEach(c => {
        c.classList.remove('border-success','bg-success','bg-opacity-10',
                           'border-warning','bg-warning',
                           'border-danger','bg-danger');
    });
    const map = { pagado: ['border-success','bg-success','bg-opacity-10'],
                  abono:  ['border-warning','bg-warning','bg-opacity-10'],
                  pendiente: ['border-danger','bg-danger','bg-opacity-10'] };
    map[val].forEach(c => el.classList.add(c));
    el.querySelector('input[type=radio]').checked = true;

    const campoAbono = document.getElementById('campo-monto-abono');
    const inputAbono = document.getElementById('input-monto-abono');
    if (val === 'abono') {
        campoAbono.classList.remove('d-none');
        inputAbono.required = true;
        actualizarSaldoRestante();
    } else {
        campoAbono.classList.add('d-none');
        inputAbono.required = false;
        inputAbono.value = '';
    }
}

function actualizarSaldoRestante() {
    const totalTexto = document.getElementById('total-venta').textContent.replace(/[^0-9]/g, '');
    const total = parseFloat(totalTexto) || 0;
    const abono = parseFloat(document.getElementById('input-monto-abono').value) || 0;
    const restante = Math.max(total - abono, 0);
    document.getElementById('texto-saldo-restante').textContent =
        total > 0 ? `Saldo restante: $${restante.toLocaleString('es-CO')}` : '';
    document.getElementById('input-monto-abono').max = total;
}

document.getElementById('input-monto-abono').addEventListener('input', actualizarSaldoRestante);

// Agregar primera fila al cargar
agregarFila();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
