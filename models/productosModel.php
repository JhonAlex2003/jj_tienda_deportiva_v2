<?php

class ProductoModel {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM productos ORDER BY id_producto DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtener($id) {
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE id_producto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function agregarProducto($data) {
        $stmt = $this->conn->prepare("INSERT INTO productos(nombre, descripcion, categoria, talla, precio, cantidad, stock_minimo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stock_minimo = $data['stock_minimo'] ?? 5;
        $stmt->bind_param(
            "ssssdii",
            $data['nombre'],
            $data['descripcion'],
            $data['categoria'],
            $data['talla'],
            $data['precio'],
            $data['cantidad'],
            $stock_minimo
        );
        return $stmt->execute();
    }

    public function actualizarProducto($id, $data) {
        $estado = isset($data['estado']) ? (int)$data['estado'] : 1;
        $stock_minimo = $data['stock_minimo'] ?? 5;
        $stmt = $this->conn->prepare("UPDATE productos SET nombre=?, descripcion=?, categoria=?, talla=?, precio=?, cantidad=?, stock_minimo=?, estado=? WHERE id_producto=?");
        $stmt->bind_param(
            "ssssdiiii",
            $data['nombre'],
            $data['descripcion'],
            $data['categoria'],
            $data['talla'],
            $data['precio'],
            $data['cantidad'],
            $stock_minimo,
            $estado,
            $id
        );
        return $stmt->execute();
    }

    // Intenta eliminar el producto físicamente. Si tiene ventas o movimientos
    // asociados, la base de datos rechaza el borrado (error 1451, integridad
    // referencial); en versiones recientes de PHP esto se lanza como excepción
    // en lugar de simplemente devolver false, así que se captura explícitamente.
    // Devuelve 'deleted', 'deactivated' o false si algo más falla.
    public function eliminarProducto($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM productos WHERE id_producto = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return 'deleted';
        } catch (mysqli_sql_exception $e) {
            // 1451 = no se puede borrar por restricción de clave foránea
            if ($e->getCode() === 1451) {
                $stmt2 = $this->conn->prepare("UPDATE productos SET estado = 0 WHERE id_producto = ?");
                $stmt2->bind_param("i", $id);
                if ($stmt2->execute()) {
                    return 'deactivated';
                }
            }
            return false;
        }
    }
}
