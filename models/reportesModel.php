<?php
class ReportesModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ventas por fecha con cliente y productos
    public function ventasPorFecha($fecha) {
        $query = "
            SELECT 
                v.id_venta,
                v.fecha_venta,
                c.nombre AS cliente,
                GROUP_CONCAT(CONCAT(p.nombre, ' (', dv.cantidad, ')') SEPARATOR ', ') AS productos,
                v.total
            FROM ventas v
            JOIN clientes c ON v.id_cliente = c.id_cliente
            JOIN detalle_venta dv ON v.id_venta = dv.id_venta
            JOIN productos p ON dv.id_producto = p.id_producto
            WHERE v.fecha_venta = ?
            GROUP BY v.id_venta
            ORDER BY v.id_venta DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener todas las fechas de ventas (para el select)
    public function getFechasVentas() {
        $query = "SELECT DISTINCT fecha_venta FROM ventas ORDER BY fecha_venta DESC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
