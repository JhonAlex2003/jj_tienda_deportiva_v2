<?php
class VentasModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Registrar una venta (ahora con estado_pago)
    public function crearVenta($id_cliente, $fecha_venta, $total, $estado_pago = 'pagado') {
        $stmt = $this->conn->prepare("INSERT INTO ventas (id_cliente, fecha_venta, total, estado_pago) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $id_cliente, $fecha_venta, $total, $estado_pago);
        $stmt->execute();
        return $this->conn->insert_id;
    }

    // Registrar detalle de venta
    public function crearDetalleVenta($id_venta, $id_producto, $cantidad, $precio_unitario) {
        $subtotal = $cantidad * $precio_unitario;
        $stmt = $this->conn->prepare("INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $id_venta, $id_producto, $cantidad, $precio_unitario, $subtotal);
        return $stmt->execute();
    }

    // Descontar cantidad de productos después de la venta
    public function descontarCantidadProducto($id_producto, $cantidad_vendida) {
        $stmt = $this->conn->prepare("UPDATE productos SET cantidad = cantidad - ? WHERE id_producto = ?");
        $stmt->bind_param("ii", $cantidad_vendida, $id_producto);
        return $stmt->execute();
    }

    // NUEVO: Registrar movimiento de inventario (salida por venta)
    public function registrarMovimiento($id_producto, $tipo_movimiento, $cantidad, $motivo, $id_venta = null) {
        $stmt = $this->conn->prepare("INSERT INTO movimientos_inventario (id_producto, tipo_movimiento, cantidad, motivo, id_venta) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isisi", $id_producto, $tipo_movimiento, $cantidad, $motivo, $id_venta);
        return $stmt->execute();
    }

    // Obtener todas las fechas de ventas (para dropdown)
    public function obtenerFechasVentas() {
        $sql = "SELECT DISTINCT DATE(fecha_venta) AS fecha FROM ventas ORDER BY fecha_venta DESC";
        $result = $this->conn->query($sql);
        $fechas = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $fechas[] = $row['fecha'];
            }
        }
        return $fechas;
    }

    // Obtener ventas por fecha específica con cliente y productos
    public function obtenerVentasPorFecha($fecha) {
        $sql = "SELECT 
                    v.id_venta, v.fecha_venta, v.total, v.estado_pago,
                    c.nombre AS cliente,
                    GROUP_CONCAT(p.nombre, ' (', dv.cantidad, ')') AS productos
                FROM ventas v
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                LEFT JOIN detalle_venta dv ON v.id_venta = dv.id_venta
                LEFT JOIN productos p ON dv.id_producto = p.id_producto
                WHERE DATE(v.fecha_venta) = ?
                GROUP BY v.id_venta
                ORDER BY v.fecha_venta DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        $res = $stmt->get_result();
        $ventas = [];
        while ($row = $res->fetch_assoc()) { $ventas[] = $row; }
        return $ventas;
    }

    // Obtener ventas por rango de fechas con cliente y productos
    public function obtenerVentasPorRango($fecha_inicio, $fecha_fin) {
        $sql = "SELECT 
                    v.id_venta, v.fecha_venta, v.total, v.estado_pago,
                    c.nombre AS cliente,
                    GROUP_CONCAT(p.nombre, ' (', dv.cantidad, ')') AS productos
                FROM ventas v
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                LEFT JOIN detalle_venta dv ON v.id_venta = dv.id_venta
                LEFT JOIN productos p ON dv.id_producto = p.id_producto
                WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
                GROUP BY v.id_venta
                ORDER BY v.fecha_venta DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
        $stmt->execute();
        $res = $stmt->get_result();
        $ventas = [];
        while ($row = $res->fetch_assoc()) { $ventas[] = $row; }
        return $ventas;
    }

    // NUEVO: Historial completo de ventas con filtros opcionales
    public function obtenerHistorialVentas($filtro_cliente = '', $filtro_fecha = '') {
        $sql = "SELECT 
                    v.id_venta, v.fecha_venta, v.total, v.estado_pago,
                    c.nombre AS cliente,
                    GROUP_CONCAT(p.nombre, ' (', dv.cantidad, ')' SEPARATOR ', ') AS productos
                FROM ventas v
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                LEFT JOIN detalle_venta dv ON v.id_venta = dv.id_venta
                LEFT JOIN productos p ON dv.id_producto = p.id_producto
                WHERE 1=1";
        $params = [];
        $types = '';

        if (!empty($filtro_cliente)) {
            $sql .= " AND c.nombre LIKE ?";
            $params[] = "%$filtro_cliente%";
            $types .= 's';
        }
        if (!empty($filtro_fecha)) {
            $sql .= " AND DATE(v.fecha_venta) = ?";
            $params[] = $filtro_fecha;
            $types .= 's';
        }
        $sql .= " GROUP BY v.id_venta ORDER BY v.fecha_venta DESC";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $ventas = [];
        while ($row = $res->fetch_assoc()) { $ventas[] = $row; }
        return $ventas;
    }

    // NUEVO: Obtener detalle completo de una venta específica
    public function obtenerDetalleVenta($id_venta) {
        $sql = "SELECT dv.*, p.nombre AS producto_nombre, p.talla
                FROM detalle_venta dv
                LEFT JOIN productos p ON dv.id_producto = p.id_producto
                WHERE dv.id_venta = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_venta);
        $stmt->execute();
        $res = $stmt->get_result();
        $detalle = [];
        while ($row = $res->fetch_assoc()) { $detalle[] = $row; }
        return $detalle;
    }
}
?>
