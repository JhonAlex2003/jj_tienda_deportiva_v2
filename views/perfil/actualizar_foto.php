<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado.']);
    exit;
}

require_once __DIR__ . '/../../controllers/usuariosController.php';

$data = json_decode(file_get_contents('php://input'), true);
$imagenBase64 = $data['imagen'] ?? null;

if (!$imagenBase64) {
    echo json_encode(['ok' => false, 'error' => 'No se recibió ninguna imagen.']);
    exit;
}

// Decodificar base64
if (preg_match('/^data:image\/(\w+);base64,/', $imagenBase64, $matches)) {
    $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
    $imagenBase64 = substr($imagenBase64, strpos($imagenBase64, ',') + 1);
} else {
    echo json_encode(['ok' => false, 'error' => 'Formato de imagen no válido.']);
    exit;
}

$imagenDecodificada = base64_decode($imagenBase64);
if ($imagenDecodificada === false) {
    echo json_encode(['ok' => false, 'error' => 'Error al procesar la imagen.']);
    exit;
}

// Carpeta de destino
$carpetaDestino = __DIR__ . '/../../assets/uploads/perfiles/';
if (!is_dir($carpetaDestino)) {
    mkdir($carpetaDestino, 0755, true);
}

// Nombre único del archivo
$id_usuario = $_SESSION['id_usuario'];
$nombreArchivo = 'user_' . $id_usuario . '_' . time() . '.' . $extension;
$rutaCompleta = $carpetaDestino . $nombreArchivo;

if (file_put_contents($rutaCompleta, $imagenDecodificada)) {
    $rutaRelativa = '/jj_tienda_deportiva/assets/uploads/perfiles/' . $nombreArchivo;

    $controller = new UsuariosController();
    if ($controller->actualizarFoto($id_usuario, $rutaRelativa)) {
        $_SESSION['foto_perfil'] = $rutaRelativa;
        echo json_encode(['ok' => true, 'ruta' => $rutaRelativa]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar en la base de datos.']);
    }
} else {
    echo json_encode(['ok' => false, 'error' => 'Error al guardar el archivo.']);
}
