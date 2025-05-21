<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit();
}

include 'php/conexion.php';

$id_usuaria = $_SESSION['usuario']['id'];

// Leer datos JSON enviados
$data = json_decode(file_get_contents('php://input'), true);

$fecha_inicio = $data['fecha_inicio'] ?? '';
$duracion = intval($data['duracion'] ?? 0);
$flujo = $data['flujo'] ?? '';
$sintomas = $data['sintomas'] ?? '';
$fecha_registro = date("Y-m-d H:i:s");

if ($fecha_inicio && $duracion > 0) {
    // Verificar si ya existe registro para esta usuaria
    $query_check = "SELECT id FROM ciclo_menstrual WHERE usuaria_id = ?";
    $stmt_check = $conexion->prepare($query_check);
    $stmt_check->bind_param("i", $id_usuaria);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Actualizar registro existente
        $row = $result_check->fetch_assoc();
        $id_ciclo = $row['id'];
        $query_update = "UPDATE ciclo_menstrual SET fecha_inicio = ?, duracion = ?, flujo = ?, sintomas = ?, fecha_registro = ? WHERE id = ? AND usuaria_id = ?";
        $stmt_update = $conexion->prepare($query_update);
        $stmt_update->bind_param("sisssii", $fecha_inicio, $duracion, $flujo, $sintomas, $fecha_registro, $id_ciclo, $id_usuaria);
        $stmt_update->execute();
        echo json_encode(["mensaje" => "Datos actualizados correctamente"]);
    } else {
        // Insertar nuevo registro
        $query_insert = "INSERT INTO ciclo_menstrual (usuaria_id, fecha_inicio, duracion, flujo, sintomas, fecha_registro) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conexion->prepare($query_insert);
        $stmt_insert->bind_param("isisss", $id_usuaria, $fecha_inicio, $duracion, $flujo, $sintomas, $fecha_registro);
        $stmt_insert->execute();
        echo json_encode(["mensaje" => "Datos guardados correctamente"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos o inválidos"]);
}
