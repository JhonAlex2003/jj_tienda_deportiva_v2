<?php
class AbonosController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Listar ventas con saldo pendiente (abono o pendiente), agrupadas con lo abonado
    public function listarPendientes() {
        $sql = "SELECT 
                    v.id_venta,
                    v.fecha_venta,
                    v.total,
                    v.estado_pago,
                    c.id_cliente,
                    c.nombre AS cliente,
                    c.telefono,
                    COALESCE(SUM(a.monto), 0) AS total_abonado
                FROM ventas v
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                LEFT JOIN abonos a ON v.id_venta = a.id_venta
                WHERE v.estado_pago IN ('abono', 'pendiente')
                GROUP BY v.id_venta
                HAVING (v.total - total_abonado) > 0
                ORDER BY v.fecha_venta DESC";
        $res = $this->conn->query($sql);
        $ventas = [];
        while ($row = $res->fetch_assoc()) {
            $row['saldo_pendiente'] = $row['total'] - $row['total_abonado'];
            $ventas[] = $row;
        }
        return $ventas;
    }

    // Resumen: total adeudado y clientes con deuda
    public function resumen() {
        $pendientes = $this->listarPendientes();
        $total_adeudado = 0;
        $clientes_con_deuda = [];
        foreach ($pendientes as $v) {
            $total_adeudado += $v['saldo_pendiente'];
            if ($v['id_cliente']) {
                $clientes_con_deuda[$v['id_cliente']] = true;
            }
        }
        return [
            'total_adeudado' => $total_adeudado,
            'total_ventas_pendientes' => count($pendientes),
            'total_clientes' => count($clientes_con_deuda)
        ];
    }

    // Obtener historial de abonos de una venta específica
    public function historialAbonos($id_venta) {
        $stmt = $this->conn->prepare("SELECT * FROM abonos WHERE id_venta = ? ORDER BY fecha_abono DESC");
        $stmt->bind_param("i", $id_venta);
        $stmt->execute();
        $res = $stmt->get_result();
        $abonos = [];
        while ($row = $res->fetch_assoc()) { $abonos[] = $row; }
        return $abonos;
    }

    // Registrar un nuevo abono
    public function registrarAbono($id_venta, $monto, $nota = null) {
        $stmt = $this->conn->prepare("INSERT INTO abonos (id_venta, monto, nota) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $id_venta, $monto, $nota);
        $ok = $stmt->execute();

        if ($ok) {
            // Verificar si con este abono ya se saldó la deuda
            $stmt2 = $this->conn->prepare("SELECT total FROM ventas WHERE id_venta = ?");
            $stmt2->bind_param("i", $id_venta);
            $stmt2->execute();
            $total = $stmt2->get_result()->fetch_assoc()['total'];

            $stmt3 = $this->conn->prepare("SELECT COALESCE(SUM(monto),0) AS abonado FROM abonos WHERE id_venta = ?");
            $stmt3->bind_param("i", $id_venta);
            $stmt3->execute();
            $abonado = $stmt3->get_result()->fetch_assoc()['abonado'];

            // Si ya se cubrió el total, marcar como pagado
            if ($abonado >= $total) {
                $stmt4 = $this->conn->prepare("UPDATE ventas SET estado_pago = 'pagado' WHERE id_venta = ?");
                $stmt4->bind_param("i", $id_venta);
                $stmt4->execute();
            }
        }
        return $ok;
    }
}
?>
