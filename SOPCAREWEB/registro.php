<?php
require_once 'php/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $edad = $_POST['edad'];
    $email = $_POST['email'];
    $usuario = $_POST['usuario'];
    $contraseña_plana = $_POST['contraseña'];

    if (!preg_match("/^[a-zA-ZÁÉÍÓÚáéíóúÑñ ]+$/", $nombre)) {
        echo "<script>
            alert('El nombre solo debe contener letras y espacios.');
            window.history.back();
        </script>";
        exit();
    }

    if (strlen($contraseña_plana) < 6) {
        echo "<script>
            alert('La contraseña debe tener al menos 6 caracteres.');
            window.history.back();
        </script>";
        exit();
    }

    $password = password_hash($contraseña_plana, PASSWORD_DEFAULT);

    $verificar = $conexion->prepare("SELECT id FROM usuarias WHERE email = ?");
    $verificar->bind_param("s", $email);
    $verificar->execute();
    $resultado = $verificar->get_result();

    if ($resultado->num_rows > 0) {
        echo "<script>
            alert('Este correo ya está registrado. Por favor inicia sesión.');
            window.location.href = 'iniciosesion.php';
        </script>";
        exit();
    }
    $verificar->close();

    $stmt = $conexion->prepare("INSERT INTO usuarias (nombre, edad, email, usuario, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $nombre, $edad, $email, $usuario, $password);

    if ($stmt->execute()) {
        echo "<script>
            alert('Registro exitoso. Ahora puedes iniciar sesión.');
            window.location.href = 'iniciosesion.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('Error al registrar. Intenta de nuevo.');</script>";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registro - SOPCare</title>
  <link rel="stylesheet" href="css/registro.css" />
  <link rel="icon" href="imagenes/icono.png" />
</head>
<body>
  <div class="registro-contenedor">
    <img src="imagenes/logo.png" alt="Logo SOPCare" class="logo-registro" />
    <h2>Crear Cuenta</h2>
    <p class="subtitulo">Regístrate para comenzar 💖</p>

    <form method="POST">
      <input type="text" name="nombre" placeholder="Nombre completo" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]{2,50}" title="Solo letras y espacios" />
      <input type="number" name="edad" placeholder="Edad" required />
      <input type="email" name="email" placeholder="Correo electrónico" required />
      <input type="text" name="usuario" placeholder="Nombre de usuaria" required />
      <input type="password" name="contraseña" placeholder="Contraseña" required minlength="6" />
      <button type="submit">Registrarme</button>
    </form>

    <p class="volver">¿Ya tienes cuenta? <a href="iniciosesion.php">Inicia sesión aquí</a></p>
  </div>
</body>
</html>

