<?php
session_start();

// Verifica si la usuaria ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: iniciosesion.php');
    exit();
}

include 'php/conexion.php'; // Conexión a la base de datos

$usuaria_id = $_SESSION['usuario']['id']; // ID de la usuaria actual

// Verifica que la solicitud sea POST y que se haya enviado un ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']); // ID del suplemento a eliminar

    // Verifica que el suplemento pertenezca a la usuaria
    $verificar = $conexion->prepare("SELECT id FROM medicaciones WHERE id = ? AND usuaria_id = ?");
    $verificar->bind_param("ii", $id, $usuaria_id);
    $verificar->execute();
    $resultado = $verificar->get_result();

    if ($resultado->num_rows > 0) {
        // Si pertenece a la usuaria, se elimina
        $eliminar = $conexion->prepare("DELETE FROM medicaciones WHERE id = ? AND usuaria_id = ?");
        $eliminar->bind_param("ii", $id, $usuaria_id);
        if ($eliminar->execute()) {
            header('Location: suplementos.php?mensaje=suplemento_eliminado');
        } else {
            header('Location: suplementos.php?error=Error+al+eliminar+el+suplemento');
        }
        $eliminar->close();
    } else {
        // Si no pertenece a la usuaria, se muestra error
        header('Location: suplementos.php?error=No+autorizada+para+eliminar+este+suplemento');
    }

    $verificar->close();
} else {
    // Si la petición no es válida, redirige con error
    header('Location: suplementos.php?error=Petición+inválida');
}

$conexion->close();
exit();
