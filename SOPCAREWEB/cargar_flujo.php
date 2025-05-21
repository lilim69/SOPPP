<?php
session_start(); // inicia la sesion para acceder a variables de sesion

include 'conexion.php'; // incluye la conexion a la base de datos

header('Content-Type: application/json'); // indica que devolvera una respuesta en formato json

// si no hay una usuaria autenticada, retorna un array vacio y termina
if (!isset($_SESSION['id_usuaria'])) {
    echo json_encode([]);
    exit;
}

$id_usuaria = $_SESSION['id_usuaria']; // guarda el id de la usuaria autenticada

// prepara la consulta para obtener fechas y flujo del ciclo menstrual de la usuaria
$sql = "SELECT fecha, flujo FROM ciclo_menstrual WHERE id_usuaria = ?";
$stmt = $conn->prepare($sql); // prepara la sentencia
$stmt->bind_param('i', $id_usuaria); // vincula el id como parametro
$stmt->execute(); // ejecuta la consulta
$result = $stmt->get_result(); // obtiene el resultado

// crea un array para guardar los datos
$datos = [];
while ($row = $result->fetch_assoc()) {
    $datos[$row['fecha']] = (int)$row['flujo']; // guarda el flujo como entero asociado a la fecha
}

echo json_encode($datos); // devuelve los datos como json
?>
