<?php
// Iniciar sesión y verificar si la usuaria está logueada
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: iniciosesion.php'); // Redirige si no está logueada
    exit();
}

// Obtener el nombre de usuario desde la sesión
$usuario = $_SESSION['usuario']['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Síntomas - SOPCare Web</title>
  <link rel="stylesheet" href="css/sintomas.css" />
</head>
<body>

  <header>
    <h1>Bienvenida, <?php echo htmlspecialchars($usuario); ?>!</h1>
  </header>

  <section class="formulario">
    <h2>¿Qué síntomas has experimentado hoy?</h2>
    <input type="date" id="fecha" />
  
    <div class="checkboxes" id="grupo-sintomas">
      <label><input type="checkbox" value="Dolor abdominal" /> Dolor abdominal</label>
      <label><input type="checkbox" value="Fatiga" /> Fatiga</label>
      <label><input type="checkbox" value="Cambios de humor" /> Cambios de humor</label>
      <label><input type="checkbox" value="Acné" /> Acné</label>
      <label><input type="checkbox" value="Aumento de peso" /> Aumento de peso</label>
      <label><input type="checkbox" value="Caída del cabello" /> Caída del cabello</label>
      <label><input type="checkbox" value="Hirsutismo" /> Crecimiento excesivo de vello</label>
      <label><input type="checkbox" value="Ciclo irregular" /> Ciclo irregular</label>
      <label><input type="checkbox" value="Inflamación" /> Hinchazón o inflamación</label>
      <label><input type="checkbox" value="Dolor de cabeza" /> Dolor de cabeza</label>
      <label><input type="checkbox" value="Insomnio" /> Dificultad para dormir</label>
      <label><input type="checkbox" value="Ansiedad" /> Ansiedad</label>
      <label><input type="checkbox" value="Tristeza" /> Tristeza</label>
      <label><input type="checkbox" value="Antojos" /> Antojos o aumento del apetito</label>
      <label><input type="checkbox" value="Piel grasa" /> Cambios en la piel</label>
    </div>
  
    <textarea id="otros" placeholder="Otros síntomas que desees registrar (opcional)"></textarea>
    <button onclick="guardarSintomas()">Registrar</button>
  </section>

  <section class="historial">
    <h2>Historial de síntomas</h2>
    <ul id="lista-sintomas"></ul>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/sintomas.js"></script>

</body>
</html>
