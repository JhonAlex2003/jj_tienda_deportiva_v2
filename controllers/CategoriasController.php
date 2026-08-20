<?php
require_once __DIR__ . '/../models/CategoriasModel.php';

class CategoriasController {
    private $model;

    public function __construct($conn) {
        $this->model = new CategoriasModel($conn);
    }

    public function listar() {
        return $this->model->getCategorias();
    }

    public function obtener($id) {
        return $this->model->getCategoriaById($id);
    }

    public function crear($nombre, $descripcion) {
        return $this->model->crearCategoria($nombre, $descripcion);
    }

    public function actualizar($id, $nombre, $descripcion) {
        return $this->model->actualizarCategoria($id, $nombre, $descripcion);
    }

    public function eliminar($id) {
        return $this->model->eliminarCategoria($id);
    }
}
?>
