<?php
session_start();
include 'php/conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: perfil.php?mensaje=guardado");
    exit();
}

$id_usuaria = $_SESSION['usuario']['id'];

// Sanitizar entradas
$nombre = $_POST['nombre'] ?? '';
$edad = $_POST['edad'] ?? '';
$peso = $_POST['peso'] ?? null;
$altura = $_POST['altura'] ?? null;
$fecha_diagnostico = $_POST['fecha_diagnostico'] ?? null;
$diagnostico_confirmado = $_POST['diagnostico_confirmado'] ?? null;
$tipo_sop = $_POST['tipo_sop'] ?? '';
$menstruacion_irregular = $_POST['menstruacion_irregular'] ?? null;
$toma_medicacion = $_POST['toma_medicacion'] ?? null;
$medicacion = isset($_POST['medicacion']) ? implode(', ', $_POST['medicacion']) : '';
$medicacion_otro = $_POST['medicacion_otro'] ?? '';
$objetivos = $_POST['objetivos'] ?? '';
$nivel_actividad = $_POST['nivel_actividad'] ?? '';
$sigue_dieta = $_POST['sigue_dieta'] ?? null;
$tipo_dieta = $_POST['tipo_dieta'] ?? '';
$tipo_dieta_otro = $_POST['tipo_dieta_otro'] ?? '';

// Agregar “otro” en medicación si fue rellenado
if (!empty($medicacion_otro)) {
    $medicacion .= (!empty($medicacion) ? ', ' : '') . $medicacion_otro;
}

// Manejar foto de perfil
$foto_nombre = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $nombre_temporal = $_FILES['foto']['tmp_name'];
    $foto_nombre = basename($_FILES['foto']['name']);
    $ruta_destino = "fotos/" . $foto_nombre;

    // Mover archivo subido
    if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
        // Foto cargada con éxito
    } else {
        $foto_nombre = ''; // Si falla, no guardar nombre
    }
}

// Preparar consulta
$sql = "UPDATE usuarias SET 
    nombre = ?, edad = ?, peso = ?, altura = ?, fecha_diagnostico = ?, 
    diagnostico_confirmado = ?, tipo_sop = ?, menstruacion_irregular = ?, 
    toma_medicacion = ?, medicacion = ?, objetivos = ?, nivel_actividad = ?, 
    sigue_dieta = ?, tipo_dieta = ?, tipo_dieta_otro = ?" . 
    (!empty($foto_nombre) ? ", foto = ?" : "") . 
    " WHERE id = ?";

$stmt = $conexion->prepare($sql);

if (!empty($foto_nombre)) {
    // Cadena con 17 letras para 17 variables
    $stmt->bind_param("siddssssssssssssi", 
        $nombre, $edad, $peso, $altura, $fecha_diagnostico, $diagnostico_confirmado,
        $tipo_sop, $menstruacion_irregular, $toma_medicacion, $medicacion, $objetivos, 
        $nivel_actividad, $sigue_dieta, $tipo_dieta, $tipo_dieta_otro, $foto_nombre, $id_usuaria
    );
} else {
    // Cadena con 16 letras para 16 variables (corregida)
    $stmt->bind_param("siddsssssssssssi", 
        $nombre, $edad, $peso, $altura, $fecha_diagnostico, $diagnostico_confirmado,
        $tipo_sop, $menstruacion_irregular, $toma_medicacion, $medicacion, $objetivos, 
        $nivel_actividad, $sigue_dieta, $tipo_dieta, $tipo_dieta_otro, $id_usuaria
    );
}

$stmt->execute();
$stmt->close();

header("Location: perfil.php?mensaje=guardado");
exit();
?>
