<?php
require_once __DIR__ . '/../models/VentasModel.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/abonosController.php';

class VentasController {
    private $modelo;
    private $conn;

    public function __construct($conn) {
        $this->modelo = new VentasModel($conn);
        $this->conn   = $conn;
    }

    public function registrarVenta($productos, $id_cliente = null, $estado_pago = 'pagado', $monto_abono = 0) {
        $fecha_venta = date('Y-m-d H:i:s');
        $total = 0;

        foreach ($productos as $p) {
            $cantidad = (float) preg_replace('/[^0-9.\-]/', '', $p['cantidad']);
            $precio   = (float) preg_replace('/[^0-9.\-]/', '', $p['precio_unitario']);
            $total += $cantidad * $precio;
        }

        $id_venta = $this->modelo->crearVenta($id_cliente, $fecha_venta, $total, $estado_pago);

        foreach ($productos as $prod) {
            $cantidad_limpia = (int) preg_replace('/[^0-9\-]/', '', $prod['cantidad']);
            $precio_limpio   = (float) preg_replace('/[^0-9.\-]/', '', $prod['precio_unitario']);

            // Registrar el detalle de la venta
            $this->modelo->crearDetalleVenta($id_venta, $prod['id_producto'], $cantidad_limpia, $precio_limpio);

            // Descontar del inventario
            $this->modelo->descontarCantidadProducto($prod['id_producto'], $cantidad_limpia);

            // Registrar el movimiento de inventario (salida por venta)
            $this->modelo->registrarMovimiento(
                $prod['id_producto'],
                'salida',
                $cantidad_limpia,
                "Venta #$id_venta",
                $id_venta
            );
        }

        // Si la venta es un abono y se recibió un monto inicial, registrarlo de una vez
        if ($estado_pago === 'abono' && $monto_abono > 0) {
            $abonosController = new AbonosController($this->conn);
            $abonosController->registrarAbono($id_venta, min($monto_abono, $total), 'Abono inicial al registrar la venta');
        }

        return $id_venta;
    }

    public function obtenerHistorialVentas($filtro_cliente = '', $filtro_fecha = '') {
        return $this->modelo->obtenerHistorialVentas($filtro_cliente, $filtro_fecha);
    }

    public function obtenerDetalleVenta($id_venta) {
        return $this->modelo->obtenerDetalleVenta($id_venta);
    }
}
?>
