<?php
class ClientesModel {

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Listar clientes
    public function listar()
    {
        $sql = "SELECT * FROM clientes ORDER BY id_cliente DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener cliente por ID
    public function obtener($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Agregar cliente
    public function agregar($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO clientes (nombre, telefono, correo, direccion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $data['nombre'], $data['telefono'], $data['correo'], $data['direccion']);
        return $stmt->execute();
    }

    // Actualizar cliente
    public function actualizar($id, $data)
    {
        $stmt = $this->conn->prepare("UPDATE clientes SET nombre=?, telefono=?, correo=?, direccion=? WHERE id_cliente=?");
        $stmt->bind_param("ssssi", $data['nombre'], $data['telefono'], $data['correo'], $data['direccion'], $id);
        return $stmt->execute();
    }

    // Eliminar cliente
    public function eliminar($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM clientes WHERE id_cliente=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
