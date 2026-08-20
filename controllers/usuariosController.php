<?php
// controllers/usuariosController.php
require_once __DIR__ . '/../models/usuariosModel.php';

class UsuariosController {
    private $modelo;

    public function __construct() {
        $this->modelo = new UsuariosModel();
    }

    // Función de login: devuelve datos del usuario si usuario y contraseña coinciden
    public function login($usuario, $contrasena) {
        $usuarioData = $this->modelo->obtenerUsuario($usuario);
        if ($usuarioData && isset($usuarioData['password'])) {
            if (password_verify($contrasena, $usuarioData['password'])) {
                return $usuarioData;
            }
        }
        return false;
    }

    // NUEVO: Obtener datos del perfil por ID
    public function obtenerPerfil($id_usuario) {
        return $this->modelo->obtenerPorId($id_usuario);
    }

    // NUEVO: Actualizar el nombre visible
    public function actualizarNombre($id_usuario, $nombre) {
        return $this->modelo->actualizarNombre($id_usuario, $nombre);
    }

    // NUEVO: Cambiar contraseña, validando la actual primero
    public function cambiarPassword($id_usuario, $password_actual, $password_nueva) {
        $usuario = $this->modelo->obtenerPorId($id_usuario);

        if (!$usuario) {
            return ['ok' => false, 'error' => 'Usuario no encontrado.'];
        }

        if (!password_verify($password_actual, $usuario['password'])) {
            return ['ok' => false, 'error' => 'La contraseña actual no es correcta.'];
        }

        if (strlen($password_nueva) < 6) {
            return ['ok' => false, 'error' => 'La nueva contraseña debe tener al menos 6 caracteres.'];
        }

        $hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $ok = $this->modelo->actualizarPassword($id_usuario, $hash);

        return $ok
            ? ['ok' => true, 'error' => null]
            : ['ok' => false, 'error' => 'Error al actualizar la contraseña.'];
    }

    // NUEVO: Actualizar la foto de perfil
    public function actualizarFoto($id_usuario, $ruta_foto) {
        return $this->modelo->actualizarFoto($id_usuario, $ruta_foto);
    }

    // NUEVO: Configurar/actualizar pregunta de seguridad
    public function configurarPreguntaSeguridad($id_usuario, $pregunta, $respuesta) {
        $respuesta_normalizada = mb_strtolower(trim($respuesta));
        $hash = password_hash($respuesta_normalizada, PASSWORD_DEFAULT);
        return $this->modelo->actualizarPreguntaSeguridad($id_usuario, $pregunta, $hash);
    }

    // NUEVO: Obtener la pregunta de seguridad para mostrarla en recuperación
    public function obtenerPreguntaParaRecuperar($usuario) {
        $data = $this->modelo->obtenerPreguntaPorUsuario($usuario);
        if (!$data || empty($data['pregunta_seguridad'])) {
            return null;
        }
        return $data;
    }

    // NUEVO: Validar respuesta y restablecer contraseña
    public function restablecerConRespuesta($usuario, $respuesta, $password_nueva) {
        $data = $this->modelo->obtenerPreguntaPorUsuario($usuario);

        if (!$data || empty($data['respuesta_seguridad'])) {
            return ['ok' => false, 'error' => 'Este usuario no tiene una pregunta de seguridad configurada.'];
        }

        $respuesta_normalizada = mb_strtolower(trim($respuesta));
        if (!password_verify($respuesta_normalizada, $data['respuesta_seguridad'])) {
            return ['ok' => false, 'error' => 'La respuesta no es correcta.'];
        }

        if (strlen($password_nueva) < 6) {
            return ['ok' => false, 'error' => 'La nueva contraseña debe tener al menos 6 caracteres.'];
        }

        $hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $ok = $this->modelo->resetearPassword($data['id_usuario'], $hash);

        return $ok
            ? ['ok' => true, 'error' => null]
            : ['ok' => false, 'error' => 'Error al restablecer la contraseña.'];
    }
}
?>
