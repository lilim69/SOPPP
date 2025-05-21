<?php
$servidor = "localhost";  // Siempre localhost en XAMPP
$usuario = "root";        // Cambia esto por tu usuario de MySQL (por defecto es 'root')
$contraseña = "";         // Cambia esto por la contraseña de MySQL (por defecto es vacío)
$base_de_datos = "sopcare"; // Nombre de tu base de datos

// Crear la conexión
$conexion = new mysqli($servidor, $usuario, $contraseña, $base_de_datos);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
