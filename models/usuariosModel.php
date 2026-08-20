<?php
// models/usuariosModel.php

require_once __DIR__ . '/../config/db.php';

class UsuariosModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // Devuelve fila completa del usuario o null
    public function obtenerUsuario($usuario) {
        $sql = "SELECT * FROM usuarios WHERE usuario = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Error en prepare: " . $this->conn->error);
        }
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // NUEVO: Obtener usuario por ID
    public function obtenerPorId($id_usuario) {
        $sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // NUEVO: Actualizar el nombre visible del usuario
    public function actualizarNombre($id_usuario, $nombre) {
        $sql = "UPDATE usuarios SET nombre = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $nombre, $id_usuario);
        return $stmt->execute();
    }

    // NUEVO: Actualizar contraseña (ya encriptada con password_hash)
    public function actualizarPassword($id_usuario, $password_hash) {
        $sql = "UPDATE usuarios SET password = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $id_usuario);
        return $stmt->execute();
    }

    // NUEVO: Actualizar la ruta de la foto de perfil
    public function actualizarFoto($id_usuario, $ruta_foto) {
        $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $ruta_foto, $id_usuario);
        return $stmt->execute();
    }

    // NUEVO: Guardar/actualizar la pregunta y respuesta de seguridad
    public function actualizarPreguntaSeguridad($id_usuario, $pregunta, $respuesta_hash) {
        $sql = "UPDATE usuarios SET pregunta_seguridad = ?, respuesta_seguridad = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $pregunta, $respuesta_hash, $id_usuario);
        return $stmt->execute();
    }

    // NUEVO: Obtener la pregunta de seguridad de un usuario por nombre de usuario
    public function obtenerPreguntaPorUsuario($usuario) {
        $sql = "SELECT id_usuario, pregunta_seguridad, respuesta_seguridad FROM usuarios WHERE usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // NUEVO: Restablecer contraseña directamente (usado tras validar la respuesta)
    public function resetearPassword($id_usuario, $password_hash) {
        $sql = "UPDATE usuarios SET password = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $id_usuario);
        return $stmt->execute();
    }
}
?>
