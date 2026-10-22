<?php
include "config/db.php";

$usuario = "admin";
$pass_plano = "admin123"; // Puedes cambiarla
$password = password_hash($pass_plano, PASSWORD_DEFAULT);
$rol = "admin";

$sql = "INSERT INTO usuarios (usuario, password, rol)
        VALUES ('$usuario', '$password', '$rol')";

if ($conn->query($sql)) {
    echo "✔ Usuario administrador creado correctamente";
} else {
    echo "❌ Error: " . $conn->error;
}
