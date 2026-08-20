<?php
class Database {
    private $host = "127.0.0.1";
    private $port = 3306;
    private $dbname = "jj_tienda_db";
    private $user = "root";
    private $pass = ""; 

    public function connect() {
        $conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->port);
        if ($conn->connect_error) {
            die("Error de conexión mysqli: " . $conn->connect_error);
        }
        return $conn;
    }
}
?>