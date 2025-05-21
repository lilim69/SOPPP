<?php
session_start(); // inicia la sesion

// si no hay una usuaria autenticada, devuelve error 401
if (!isset($_SESSION['usuario'])) {
    http_response_code(401); // codigo de error no autorizado
    echo json_encode(["error" => "no autenticado"]); // mensaje de error en json
    exit();
}

include 'php/conexion.php'; // incluye la conexion a la base de datos

$id_usuaria = $_SESSION['usuario']['id']; // obtiene el id de la usuaria desde la sesion

// si se recibe una solicitud tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true); // decodifica el json recibido

    $fecha = $input['fecha'] ?? ''; // obtiene la fecha o vacio si no viene
    $flujo = $input['flujo'] ?? 'ninguno'; // obtiene el flujo o ninguno si no viene
    $fecha_registro = date("Y-m-d H:i:s"); // obtiene la fecha actual

    // valida que la fecha no este vacia
    if (!$fecha) {
        http_response_code(400); // error por solicitud incorrecta
        echo json_encode(["error" => "falta la fecha"]);
        exit();
    }

    // valida que el flujo sea un valor permitido
    if (!in_array($flujo, ['ninguno', 'leve', 'moderado', 'abundante'])) {
        http_response_code(400);
        echo json_encode(["error" => "valor de flujo invalido"]);
        exit();
    }

    // verifica si ya existe un registro con esa fecha para la usuaria
    $query_check = "SELECT id FROM ciclo_menstrual WHERE usuaria_id = ? AND fecha = ?";
    $stmt_check = $conexion->prepare($query_check);
    $stmt_check->bind_param("is", $id_usuaria, $fecha);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // si ya existe, actualiza el registro existente
        $row = $result_check->fetch_assoc();
        $id = $row['id'];

        $query_update = "UPDATE ciclo_menstrual SET flujo = ?, fecha_registro = ? WHERE id = ?";
        $stmt_update = $conexion->prepare($query_update);
        $stmt_update->bind_param("ssi", $flujo, $fecha_registro, $id);
        $stmt_update->execute();

        echo json_encode(["mensaje" => "registro actualizado"]);
    } else {
        // si no existe, inserta un nuevo registro
        $query_insert = "INSERT INTO ciclo_menstrual (usuaria_id, fecha, flujo, fecha_registro) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conexion->prepare($query_insert);
        $stmt_insert->bind_param("isss", $id_usuaria, $fecha, $flujo, $fecha_registro);
        $stmt_insert->execute();

        echo json_encode(["mensaje" => "registro guardado"]);
    }
    exit(); // termina el script
}

// si la solicitud no es post, ejecuta una consulta para obtener los datos existentes
$query = "SELECT fecha, flujo FROM ciclo_menstrual WHERE usuaria_id = ? ORDER BY fecha";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_usuaria);
$stmt->execute();
$result = $stmt->get_result();

$ciclo = []; // array para guardar los datos del ciclo
while ($row = $result->fetch_assoc()) {
    $ciclo[$row['fecha']] = $row['flujo']; // asocia flujo a la fecha
}
?>
<!DOCTYPE html> <!-- define el tipo de documento como html5 -->
<html lang="es"> <!-- establece el idioma del documento como español -->
<head>
  <meta charset="UTF-8" /> <!-- define el conjunto de caracteres como utf-8 -->
  <meta name="viewport" content="width=device-width, initial-scale=1" /> <!-- ajusta la vista para dispositivos moviles -->
  <title>calendario ciclo menstrual</title> <!-- titulo que se muestra en la pestaña del navegador -->
  <link rel="stylesheet" href="css/ciclo.css" /> <!-- enlace al archivo de estilos css -->
</head>
<body>
  <header>
    <h1>calendario ciclo menstrual</h1> <!-- titulo principal de la pagina -->
  </header>

  <!-- boton para volver al menu principal -->
  <a href="principal.php" class="btn-dashboard">Volver al menú</a>

  <main>
    <!-- seccion para navegar entre meses del calendario -->
    <section class="navegacion-calendario">
      <button id="btnPrev">&lt; anterior</button> <!-- boton para ir al mes anterior -->
      <h2 id="nombre-mes"></h2> <!-- contenedor donde se mostrara el nombre del mes -->
      <button id="btnNext">siguiente &gt;</button> <!-- boton para ir al mes siguiente -->
    </section>

    <!-- contenedor donde se renderiza el calendario desde javascript -->
    <div id="calendario"></div>
  </main>

  <script>
    // define una variable en js con los datos que vienen del backend php
    const datosGuardados = <?php echo json_encode($ciclo); ?>;
  </script>

  <!-- libreria para mostrar alertas elegantes -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- script principal que maneja el calendario -->
  <script src="js/ciclo.js"></script>
</body>
</html>
