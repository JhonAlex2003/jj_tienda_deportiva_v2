<?php
class MovimientosController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Registrar un movimiento manual (entrada, salida o ajuste)
    public function registrar($id_producto, $tipo_movimiento, $cantidad, $motivo = null, $id_venta = null) {
        $stmt = $this->conn->prepare("INSERT INTO movimientos_inventario (id_producto, tipo_movimiento, cantidad, motivo, id_venta) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isisi", $id_producto, $tipo_movimiento, $cantidad, $motivo, $id_venta);
        return $stmt->execute();
    }

    // Listar todos los movimientos con nombre del producto, con filtros opcionales
    public function listar($filtro_producto = '', $filtro_tipo = '', $filtro_fecha = '') {
        $sql = "SELECT m.id_movimiento, m.tipo_movimiento, m.cantidad, m.motivo, m.fecha_movimiento,
                       p.nombre AS producto_nombre, p.talla
                FROM movimientos_inventario m
                LEFT JOIN productos p ON m.id_producto = p.id_producto
                WHERE 1=1";
        $params = [];
        $types = '';

        if (!empty($filtro_producto)) {
            $sql .= " AND p.nombre LIKE ?";
            $params[] = "%$filtro_producto%";
            $types .= 's';
        }
        if (!empty($filtro_tipo)) {
            $sql .= " AND m.tipo_movimiento = ?";
            $params[] = $filtro_tipo;
            $types .= 's';
        }
        if (!empty($filtro_fecha)) {
            $sql .= " AND DATE(m.fecha_movimiento) = ?";
            $params[] = $filtro_fecha;
            $types .= 's';
        }

        $sql .= " ORDER BY m.fecha_movimiento DESC LIMIT 200";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $movimientos = [];
        while ($row = $res->fetch_assoc()) { $movimientos[] = $row; }
        return $movimientos;
    }

    // Registrar entrada manual de mercancía (compra/reposición)
    public function registrarEntrada($id_producto, $cantidad, $motivo) {
        $this->conn->begin_transaction();
        try {
            // Actualizar cantidad en productos
            $stmt = $this->conn->prepare("UPDATE productos SET cantidad = cantidad + ? WHERE id_producto = ?");
            $stmt->bind_param("ii", $cantidad, $id_producto);
            $stmt->execute();

            // Registrar el movimiento
            $this->registrar($id_producto, 'entrada', $cantidad, $motivo);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    // Registrar ajuste manual (corrección de inventario)
    public function registrarAjuste($id_producto, $cantidad_nueva, $motivo) {
        $this->conn->begin_transaction();
        try {
            // Obtener cantidad actual
            $stmt = $this->conn->prepare("SELECT cantidad FROM productos WHERE id_producto = ?");
            $stmt->bind_param("i", $id_producto);
            $stmt->execute();
            $actual = $stmt->get_result()->fetch_assoc()['cantidad'];

            $diferencia = $cantidad_nueva - $actual;

            // Actualizar cantidad
            $stmt = $this->conn->prepare("UPDATE productos SET cantidad = ? WHERE id_producto = ?");
            $stmt->bind_param("ii", $cantidad_nueva, $id_producto);
            $stmt->execute();

            // Registrar el ajuste (con la diferencia absoluta)
            $this->registrar($id_producto, 'ajuste', abs($diferencia), $motivo . " (de $actual a $cantidad_nueva)");

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}
?>
