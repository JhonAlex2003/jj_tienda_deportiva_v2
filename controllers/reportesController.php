<?php
require_once __DIR__ . '/../models/VentasModel.php';

class ReportesController {
    private $ventasModel;

    public function __construct($conn) {
        $this->ventasModel = new VentasModel($conn);
    }

    // Devuelve un array de fechas para el select
    public function obtenerFechas() {
        return $this->ventasModel->obtenerFechasVentas();
    }

    // Devuelve ventas por fecha con cliente y productos
    public function ventasPorFecha($fecha) {
        return $this->ventasModel->obtenerVentasPorFecha($fecha);
    }

    // Nuevo: ventas por rango de fechas
    public function ventasPorRango($fecha_inicio, $fecha_fin) {
        return $this->ventasModel->obtenerVentasPorRango($fecha_inicio, $fecha_fin);
    }
}
?>
