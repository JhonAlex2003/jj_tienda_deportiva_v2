<?php
class CategoriasModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Listar todas las categorías
    public function getCategorias() {
        $sql = "SELECT * FROM categorias ORDER BY id_categoria DESC";
        $result = $this->conn->query($sql);
        $categorias = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row;
            }
        }
        return $categorias;
    }

    // Obtener una categoría por ID
    public function getCategoriaById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM categorias WHERE id_categoria = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Crear categoría
    public function crearCategoria($nombre, $descripcion) {
        $stmt = $this->conn->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        return $stmt->execute();
    }

    // Actualizar categoría
    public function actualizarCategoria($id, $nombre, $descripcion) {
        $stmt = $this->conn->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id_categoria = ?");
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);
        return $stmt->execute();
    }

    // Eliminar categoría
    public function eliminarCategoria($id) {
        $stmt = $this->conn->prepare("DELETE FROM categorias WHERE id_categoria = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>

