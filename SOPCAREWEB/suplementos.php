<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: iniciosesion.php');
    exit();
}

include 'php/conexion.php';

// Obtener el nombre de usuario desde la sesión
$nombreUsuario = $_SESSION['usuario']['usuario'];

// Obtener el id de la usuaria desde la tabla usuarias
$id_usuaria = null;
$queryId = "SELECT id FROM usuarias WHERE usuario = ?";
$stmtId = $conexion->prepare($queryId);
$stmtId->bind_param("s", $nombreUsuario);
$stmtId->execute();
$resultId = $stmtId->get_result();
if ($row = $resultId->fetch_assoc()) {
    $id_usuaria = $row['id'];
}
$stmtId->close();

// Si no se encontró el id, evitar continuar
if (!$id_usuaria) {
    die("Error: No se pudo identificar a la usuaria.");
}

// Obtener suplementos registrados por esta usuaria
$query = "SELECT * FROM medicaciones WHERE usuaria_id = ? ORDER BY id DESC";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_usuaria);
$stmt->execute();
$resultado = $stmt->get_result();
$medicaciones = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registro de Suplementos - SOPCare Web</title>
  <link rel="stylesheet" href="css/suplementos.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

  <header>
    <div class="contenedor-global">
      <h1 class="bienvenida">Bienvenida, <?php echo htmlspecialchars($nombreUsuario); ?>!</h1>
      <h2>Registro de Suplementos</h2>
    </div>
  </header>

  <main>
    <div class="contenedor-principal">
      <section class="formulario">
        <h2>Agregar Suplemento</h2>
        <form id="formSuplemento" method="POST" action="guardar_suplemento.php">
          <label for="nombre">Nombre:</label>
          <input type="text" id="nombre" name="nombre" placeholder="Ej: Vitamina D" required />

          <label for="dosis">Dosis:</label>
          <input type="text" id="dosis" name="dosis" placeholder="Ej: 500mg" required />

          <label for="tipo">Tipo:</label>
          <select id="tipo" name="tipo" required>
            <option value="">Selecciona tipo</option>
            <option value="Vitamina">Vitamina</option>
            <option value="Mineral">Mineral</option>
            <option value="Medicamento">Medicamento</option>
            <option value="Otro">Otro</option>
          </select>

          <label for="frecuencia">Frecuencia:</label>
          <select id="frecuencia" name="frecuencia" required>
            <option value="">Selecciona</option>
            <option value="Diario">Diario</option>
            <option value="Cada 2 días">Cada 2 días</option>
            <option value="Semanal">Semanal</option>
          </select>

          <label for="hora">Hora de consumo:</label>
          <input type="time" id="hora" name="hora" required />

          <button type="submit">Registrar</button>
          <div class="boton-dashboard-container">
            <a href="principal.php" class="btn-dashboard">Volver al menú</a>
          </div>
        </form>
      </section>

      <section class="lista">
        <h2>Historial de Suplementos Registrados</h2>

        <?php if (count($medicaciones) > 0): ?>
          <table class="tabla-suplementos">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Dosis</th>
                <th>Tipo</th>
                <th>Frecuencia</th>
                <th>Hora</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($medicaciones as $med): ?>
                <tr>
                  <td><?php echo htmlspecialchars($med['nombre']); ?></td>
                  <td><?php echo htmlspecialchars($med['dosis']); ?></td>
                  <td><?php echo htmlspecialchars($med['tipo']); ?></td>
                  <td><?php echo htmlspecialchars($med['frecuencia']); ?></td>
                  <td><?php echo htmlspecialchars(date('H:i', strtotime($med['hora']))); ?></td>
                  <td>
                    <form method="GET" action="editar_suplemento.php" style="display:inline;">
                      <input type="hidden" name="id" value="<?php echo $med['id']; ?>">
                      <button type="submit" class="btn-editar">Editar</button>
                    </form>

                    <form method="POST" action="eliminar_suplemento.php" style="display:inline;" onsubmit="return confirm('¿Estás segura de eliminar este suplemento?');">
                      <input type="hidden" name="id" value="<?php echo $med['id']; ?>">
                      <button type="submit" class="eliminar">Eliminar</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>Aún no has registrado suplementos.</p>
        <?php endif; ?>
      </section>
    </div>
  </main>

  <?php if (isset($_GET['exito'])): ?>
    <script>
      Swal.fire('¡Éxito!', '<?php echo htmlspecialchars($_GET['exito']); ?>', 'success');
    </script>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <script>
      Swal.fire('Error', '<?php echo htmlspecialchars($_GET['error']); ?>', 'error');
    </script>
  <?php endif; ?>

</body>
</html>
