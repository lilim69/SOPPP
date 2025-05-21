<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode([]);
    exit();
}

include 'php/conexion.php';

$id_usuaria = $_SESSION['usuario']['id'];

$stmt = $conexion->prepare("SELECT * FROM sintomas WHERE usuaria_id = ? ORDER BY fecha DESC");
$stmt->bind_param("i", $id_usuaria);
$stmt->execute();
$result = $stmt->get_result();

$sintomas = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conexion->close();

echo json_encode($sintomas);
?>
