<?php
session_start();

// Verifica si la usuaria ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: iniciosesion.php');
    exit();
}

include 'php/conexion.php'; // Conexión a la base de datos

$usuaria_id = $_SESSION['usuario']['id']; // ID de la usuaria actual

// Si se envió el formulario por método POST (actualización del suplemento)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $dosis = $_POST['dosis'];
    $tipo = $_POST['tipo'];
    $frecuencia = $_POST['frecuencia'];
    $hora = $_POST['hora'];

    // Consulta para actualizar el suplemento en la base de datos
    $query = "UPDATE medicaciones SET nombre = ?, dosis = ?, tipo = ?, frecuencia = ?, hora = ? WHERE id = ? AND usuaria_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssssssi", $nombre, $dosis, $tipo, $frecuencia, $hora, $id, $usuaria_id);

    // Ejecutar la consulta y redirigir según resultado
    if ($stmt->execute()) {
        header('Location: suplementos.php?mensaje=suplemento_actualizado');
    } else {
        echo "Error al actualizar.";
    }

    $stmt->close();
    exit();
}

// Si no se ha enviado el formulario, cargar los datos del suplemento para edición
$id = $_GET['id'] ?? 0;
$query = "SELECT * FROM medicaciones WHERE id = ? AND usuaria_id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $id, $usuaria_id);
$stmt->execute();
$result = $stmt->get_result();
$suplemento = $result->fetch_assoc();
$stmt->close();

// Si no se encuentra el suplemento, mostrar mensaje y detener ejecución
if (!$suplemento) {
    echo "No se encontró el suplemento.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Suplemento</title>
  <link rel="stylesheet" href="css/suplementos.css"> <!-- Estilos CSS -->
</head>
<body>
  <div class="contenedor">
    <h2>Editar Suplemento</h2>
    <form method="POST">
      <!-- Campo oculto para enviar el ID del suplemento -->
      <input type="hidden" name="id" value="<?php echo $suplemento['id']; ?>">

      <label>Nombre:</label>
      <input type="text" name="nombre" value="<?php echo htmlspecialchars($suplemento['nombre']); ?>" required>

      <label>Dosis:</label>
      <input type="text" name="dosis" value="<?php echo htmlspecialchars($suplemento['dosis']); ?>" required>

      <label>Tipo:</label>
      <select name="tipo" required>
        <?php
        $tipos = ['Vitamina', 'Mineral', 'Medicamento', 'Otro'];
        foreach ($tipos as $t) {
            $selected = $suplemento['tipo'] == $t ? 'selected' : '';
            echo "<option value=\"$t\" $selected>$t</option>";
        }
        ?>
      </select>

      <label>Frecuencia:</label>
      <select name="frecuencia" required>
        <?php
        $frecuencias = ['Diario', 'Cada 2 días', 'Semanal'];
        foreach ($frecuencias as $f) {
            $selected = $suplemento['frecuencia'] == $f ? 'selected' : '';
            echo "<option value=\"$f\" $selected>$f</option>";
        }
        ?>
      </select>

      <label>Hora:</label>
      <input type="time" name="hora" value="<?php echo htmlspecialchars($suplemento['hora']); ?>" required>

      <button type="submit">Guardar Cambios</button>
      <a href="suplementos.php" style="margin-left: 10px;">Cancelar</a>
    </form>
  </div>
</body>
</html>
