<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: iniciosesion.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel Principal - SOPCare</title>
  <link rel="stylesheet" href="css/dashboard.css" />
  <link rel="icon" href="imagenes/icono.png" />
</head>
<body>
  <header>
    <img src="imagenes/logo.png" alt="Logo SOPCare" class="logo" />
    <h1>Bienvenida, <?php echo $_SESSION['usuario']['nombre']; ?> 💖</h1>
    <button onclick="cerrarSesion()">Cerrar sesión</button>
  </header>

  <section class="modulos">
    <div class="tarjeta" onclick="irA('ciclo.php')">Ciclo Menstrual</div>
    <div class="tarjeta" onclick="irA('sintomas.php')">Registro de Síntomas</div>
    <div class="tarjeta" onclick="irA('suplementos.php')">Suplementos</div>
    <div class="tarjeta" onclick="irA('foro.php')">Foro</div>
    <div class="tarjeta" onclick="irA('perfil.php')">Mi Perfil</div>
  </section>

  <footer>
    &copy; 2025 SOPCare. Cuidando tu salud femenina con amor 💗
  </footer>

  <script>
    function irA(pagina) {
      window.location.href = pagina;
    }

    function cerrarSesion() {
      // Borrar la sesión y redirigir al inicio
      window.location.href = "logout.php";  // Crear archivo logout.php para destruir la sesión
    }
  </script>
</body>
</html>
