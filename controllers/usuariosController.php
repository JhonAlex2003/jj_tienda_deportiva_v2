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

    // ----- Gestión de usuarios y roles (solo administrador) -----

    public function listarUsuarios() {
        return $this->modelo->listarUsuarios();
    }

    public function crearUsuario($usuario, $nombre, $password, $rol) {
        $usuario = trim($usuario);
        $nombre  = trim($nombre);

        if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $usuario)) {
            return ['ok' => false, 'error' => 'El usuario debe tener entre 3 y 30 caracteres (letras, números, punto, guion o guion bajo).'];
        }
        if ($nombre === '' || mb_strlen($nombre) > 100) {
            return ['ok' => false, 'error' => 'Escribe el nombre visible (máximo 100 caracteres).'];
        }
        if (strlen($password) < 6) {
            return ['ok' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres.'];
        }
        if (!in_array($rol, ['admin', 'vendedor'], true)) {
            return ['ok' => false, 'error' => 'Rol no válido.'];
        }
        if ($this->modelo->existeUsuario($usuario)) {
            return ['ok' => false, 'error' => 'Ya existe un usuario con ese nombre.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->modelo->crearUsuario($usuario, $nombre, $hash, $rol)
            ? ['ok' => true, 'error' => null]
            : ['ok' => false, 'error' => 'No se pudo crear el usuario.'];
    }

    public function cambiarRol($id_usuario, $rol, $id_actual) {
        if (!in_array($rol, ['admin', 'vendedor'], true)) {
            return ['ok' => false, 'error' => 'Rol no válido.'];
        }
        if ($id_usuario === (int)$id_actual) {
            return ['ok' => false, 'error' => 'No puedes cambiar tu propio rol.'];
        }
        $objetivo = $this->modelo->obtenerPorId($id_usuario);
        if (!$objetivo) {
            return ['ok' => false, 'error' => 'El usuario no existe.'];
        }
        if ($objetivo['rol'] === 'admin' && $rol !== 'admin' && $this->modelo->contarAdmins() <= 1) {
            return ['ok' => false, 'error' => 'Debe existir al menos un administrador.'];
        }
        return $this->modelo->cambiarRol($id_usuario, $rol)
            ? ['ok' => true, 'error' => null]
            : ['ok' => false, 'error' => 'No se pudo actualizar el rol.'];
    }

    public function restablecerPasswordAdmin($id_usuario, $password_nueva) {
        if (strlen($password_nueva) < 6) {
            return ['ok' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres.'];
        }
        if (!$this->modelo->obtenerPorId($id_usuario)) {
            return ['ok' => false, 'error' => 'El usuario no existe.'];
        }
        $hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        return $this->modelo->resetearPassword($id_usuario, $hash)
            ? ['ok' => true, 'error' => null]
            : ['ok' => false, 'error' => 'No se pudo restablecer la contraseña.'];
    }

    public function eliminarUsuario($id_usuario, $id_actual) {
        if ($id_usuario === (int)$id_actual) {
            return ['ok' => false, 'error' => 'No puedes eliminar tu propio usuario.'];
        }
        $objetivo = $this->modelo->obtenerPorId($id_usuario);
        if (!$objetivo) {
            return ['ok' => false, 'error' => 'El usuario no existe.'];
        }
        if ($objetivo['rol'] === 'admin' && $this->modelo->contarAdmins() <= 1) {
            return ['ok' => false, 'error' => 'Debe existir al menos un administrador.'];
        }
        return $this->modelo->eliminarUsuario($id_usuario)
            ? ['ok' => true, 'error' => null]
            : ['ok' => false, 'error' => 'No se pudo eliminar el usuario.'];
    }
}
?>
