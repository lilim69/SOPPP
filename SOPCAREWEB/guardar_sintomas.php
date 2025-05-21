<?php
session_start();
header('Content-Type: application/json');

// Validar sesión
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit();
}

include 'php/conexion.php'; // Ajusta ruta si es diferente

$id_usuaria = $_SESSION['usuario']['id'] ?? null;
if (!$id_usuaria) {
    echo json_encode(['error' => 'ID de usuaria no encontrado']);
    exit();
}

// Recibir datos JSON
$data = json_decode(file_get_contents('php://input'), true);

$fecha = $data['fecha'] ?? null;
$sintomas = $data['sintomas'] ?? [];
$otros = trim($data['otros'] ?? '');

if (!$fecha) {
    echo json_encode(['error' => 'Fecha requerida']);
    exit();
}

// Agregar "otros" síntomas si hay
if ($otros !== '') {
    $sintomas[] = $otros;
}

if (empty($sintomas)) {
    echo json_encode(['error' => 'Debe seleccionar al menos un síntoma o escribir uno']);
    exit();
}

$sintomas_json = json_encode($sintomas);

// Insertar en BD
$stmt = $conexion->prepare("INSERT INTO sintomas (usuaria_id, fecha, sintomas) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $id_usuaria, $fecha, $sintomas_json);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Error al guardar síntomas']);
}

$stmt->close();
$conexion->close();
?>
