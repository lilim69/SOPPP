<?php
require_once 'php/conexion.php';
session_start();

session_unset();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];

    $stmt = $conexion->prepare("SELECT * FROM usuarias WHERE email = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuaria = $resultado->fetch_assoc();

        if (password_verify($contraseña, $usuaria['password'])) {
            // Guardar toda la fila para sesión
            $_SESSION['usuario'] = $usuaria;
            $_SESSION['id'] = $usuaria['id'];
            header("Location: principal.php");
            exit();
        } else {
            $error = "Correo o contraseña incorrectos.";
        }
    } else {
        $error = "Correo no registrado.";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Iniciar Sesión - SOPCare</title>
  <link rel="stylesheet" href="css/iniciosesion.css" />
  <link rel="icon" href="imagenes/icono.png" />
</head>
<body>
  <div class="login-contenedor">
    <img src="imagenes/logo.png" alt="Logo SOPCare" class="logo-login" width="60" height="70"/>
    <h2>Iniciar Sesión</h2>
    <p class="subtitulo">Bienvenida de nuevo 💖</p>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
      <input type="email" name="correo" placeholder="Correo electrónico" required />
      <input type="password" name="contraseña" placeholder="Contraseña" required />
      <button type="submit">Ingresar</button>
    </form>

    <p class="volver">¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
  </div>
</body>
</html>
