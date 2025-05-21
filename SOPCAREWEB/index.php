<?php
session_start();

if (isset($_SESSION['usuario'])) {
    $nombre_usuario = $_SESSION['usuario']['nombre'];  // Aquí usa 'nombre' para mostrar nombre completo
    $foto_perfil = $_SESSION['usuario']['foto'] ?? ''; // Si tienes foto en sesión
} else {
    $nombre_usuario = null;
    $foto_perfil = null;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>SOPCare - Bienvenida</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="icon" href="imagenes/icono.png">
</head>
<body>
  <header>
    <img src="imagenes/logo.png" alt="Logo de SOPCare" class="logo">
    <h1>SOPCare</h1>
    <p class="frase-motivacional">Tu bienestar, nuestra prioridad</p>

    <?php if ($nombre_usuario): ?>
        <!-- Mostrar el nombre de la usuaria y su foto si está logueada -->
        <div class="bienvenida">
            <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" class="foto-perfil">
            <p>Bienvenida, <?php echo $nombre_usuario; ?>!</p>
        </div>
    <?php endif; ?>
  </header>

  <main class="inicio">
    <h2>¿Cómo deseas continuar?</h2>
    <div class="botones-inicio">
      <?php if (!$nombre_usuario): ?>
          <!-- Si no está logueada, mostrar botones para registrarse o iniciar sesión -->
          <button onclick="window.location.href='registro.php'">Registrarse</button>
          <button onclick="window.location.href='iniciosesion.php'">Iniciar Sesión</button>
      <?php else: ?>
          <!-- Si está logueada, redirigir al área principal -->
          <button onclick="window.location.href='principal.php'">Ir al panel de usuario</button>
          <a href="php/logout.php"><button>Cerrar sesión</button></a>
      <?php endif; ?>
    </div>
  </main>

  <footer>
    &copy; 2025 SOPCare. Todos los derechos reservados.
  </footer>
</body>
</html>

