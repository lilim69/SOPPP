<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: iniciosesion.php');
    exit();
}

include 'php/conexion.php'; // ruta desde raíz a la carpeta php

$usuaria = $_SESSION['usuario'];
$id_usuaria = $usuaria['id'];

$nombre = $_POST['nombre'] ?? '';
$dosis = $_POST['dosis'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$frecuencia = $_POST['frecuencia'] ?? '';
$hora = $_POST['hora'] ?? '';

if (!$nombre || !$dosis || !$tipo || !$frecuencia || !$hora) {
    header('Location: suplementos.php?error=Por+favor+complete+todos+los+campos');
    exit();
}

$stmt = $conexion->prepare("INSERT INTO medicaciones (usuaria_id, nombre, dosis, tipo, frecuencia, hora) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $id_usuaria, $nombre, $dosis, $tipo, $frecuencia, $hora);

if ($stmt->execute()) {
    header('Location: suplementos.php?exito=Suplemento+registrado+con+éxito');
} else {
    header('Location: suplementos.php?error=Error+al+guardar+el+suplemento');
}
$stmt->close();
$conexion->close();
exit();
