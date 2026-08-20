<?php
require_once __DIR__ . '/../models/productosModel.php';

class ProductosController {
    private $model;

    public function __construct($db) {
        $this->model = new ProductoModel($db);
    }

    public function listar() {
        return $this->model->listar();
    }

    public function obtener($id) {
        return $this->model->obtener($id);
    }

    public function agregar($data) {
        return $this->model->agregarProducto($data);
    }

    public function actualizar($id, $data) {
        return $this->model->actualizarProducto($id, $data);
    }

    public function eliminar($id) {
        return $this->model->eliminarProducto($id);
    }
}
