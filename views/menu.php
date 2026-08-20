<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú Principal</title>
    <style>
        body { font-family: Arial; background: #f2f2f2; }
        .container {
            width: 90%; max-width: 900px; margin: 50px auto;
            background: white; padding: 30px; border-radius: 10px;
            box-shadow: 0 0 10px #999;
        }
        h2 { text-align: center; }
        .menu {
            display: grid; grid-template-columns: repeat(2, 1fr);
            gap: 25px; margin-top: 30px;
        }
        a {
            padding: 20px; display: block; background: #007bff;
            color: white; text-align: center; border-radius: 8px;
            font-size: 18px; text-decoration: none;
        }
        a:hover { background: #0056b3; }
        .logout { text-align: center; margin-top: 30px; }
        .logout a { background: red !important; }
    </style>
</head>

<body>
<div class="container">

    <h2>Menú Principal</h2>
    <p style="text-align:center;">Bienvenido, <b><?php echo $_SESSION["usuario"]; ?></b></p>

    <div class="menu">
        <a href="views/productos/listar_productos.php">Listar Productos</a>
        <a href="views/actualizar_producto.php"> Actualizar Productos</a>
        <a href="views/agregar_producto.php">Agregar Producto</a>
        <a href="views/eliminar_producto.php">Eliminar Producto</a>
        <a href="views/reportes.php">Reportes</a>
    </div>

    <div class="logout">
        <a href="logout.php">Cerrar Sesión</a>
    </div>

</div>
</body>
</html>
