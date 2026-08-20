
<?php
require_once __DIR__ . '/../models/clientesModel.php';

class ClientesController {

    private $model;

    public function __construct($db)
    {
        $this->model = new ClientesModel($db);
    }

    public function listar()
    {
        return $this->model->listar();
    }

    public function obtener($id)
    {
        return $this->model->obtener($id);
    }

    public function agregar($data)
    {
        return $this->model->agregar($data);
    }

    public function actualizar($id, $data)
    {
        return $this->model->actualizar($id, $data);
    }

    public function eliminar($id)
    {
        return $this->model->eliminar($id);
    }
}
