<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: iniciosesion.php");
    exit();
}

include 'php/conexion.php';
$id_usuaria = $_SESSION['usuario']['id'];

// --- 1. EDITAR PUBLICACIÓN: cargar datos para editar ---
$editar_publicacion_id = null;
$editar_contenido = '';
$editar_imagen = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_publicacion'])) {
    $editar_publicacion_id = intval($_POST['editar_publicacion_id']);
    // Validar que la publicación pertenece al usuario
    $stmt = $conexion->prepare("SELECT contenido, imagen FROM publicaciones WHERE id = ? AND id_usuaria = ?");
    $stmt->bind_param("ii", $editar_publicacion_id, $id_usuaria);
    $stmt->execute();
    $resultado_editar = $stmt->get_result();
    if ($resultado_editar->num_rows === 1) {
        $fila = $resultado_editar->fetch_assoc();
        $editar_contenido = $fila['contenido'];
        $editar_imagen = $fila['imagen'];
    } else {
        $editar_publicacion_id = null; // no permite editar si no es suya
    }
    $stmt->close();
}

// --- 2. GUARDAR CAMBIOS DE LA EDICIÓN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_cambios'])) {
    $id_publicacion_editar = intval($_POST['id_publicacion_editar']);
    $contenido_editado = trim($_POST['contenido_editado']);

    // Validar que la publicación pertenece al usuario
    $stmt_valida = $conexion->prepare("SELECT imagen FROM publicaciones WHERE id = ? AND id_usuaria = ?");
    $stmt_valida->bind_param("ii", $id_publicacion_editar, $id_usuaria);
    $stmt_valida->execute();
    $res_valida = $stmt_valida->get_result();

    if ($res_valida->num_rows === 1) {
        $fila_valida = $res_valida->fetch_assoc();
        $imagen_actual = $fila_valida['imagen'];
        $stmt_valida->close();

        // Manejar nueva imagen si se subió
        $imagen_nueva_path = $imagen_actual;
        if (!empty($_FILES['imagen_editada']['name'])) {
            $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['imagen_editada']['type'], $permitidos)) {
                $carpeta = 'uploads/';
                if (!is_dir($carpeta)) {
                    mkdir($carpeta, 0777, true);
                }
                $nombreArchivo = uniqid() . '_' . basename($_FILES['imagen_editada']['name']);
                $rutaDestino = $carpeta . $nombreArchivo;
                if (move_uploaded_file($_FILES['imagen_editada']['tmp_name'], $rutaDestino)) {
                    $imagen_nueva_path = $rutaDestino;
                    // Opcional: borrar imagen antigua si existe y es diferente
                    if ($imagen_actual && file_exists($imagen_actual) && $imagen_actual != $imagen_nueva_path) {
                        unlink($imagen_actual);
                    }
                }
            }
        }

        // Actualizar en base de datos
        $stmt_upd = $conexion->prepare("UPDATE publicaciones SET contenido = ?, imagen = ? WHERE id = ? AND id_usuaria = ?");
        $stmt_upd->bind_param("ssii", $contenido_editado, $imagen_nueva_path, $id_publicacion_editar, $id_usuaria);
        $stmt_upd->execute();
        $stmt_upd->close();

        header("Location: foro.php");
        exit();
    }
}

// --- 3. CREAR NUEVA PUBLICACIÓN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_publicacion'])) {
    $contenido = trim($_POST['contenido']);

    // Manejo imagen (opcional)
    $imagen_path = '';
    if (!empty($_FILES['imagen']['name'])) {
        $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['imagen']['type'], $permitidos)) {
            $carpeta = 'uploads/';
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0777, true);
            }
            $nombreArchivo = uniqid() . '_' . basename($_FILES['imagen']['name']);
            $rutaDestino = $carpeta . $nombreArchivo;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
                $imagen_path = $rutaDestino;
            }
        }
    }

    if ($contenido !== '') {
        $stmt = $conexion->prepare("INSERT INTO publicaciones (id_usuaria, contenido, imagen, fecha_publicacion) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $id_usuaria, $contenido, $imagen_path);
        $stmt->execute();
        $stmt->close();
        header("Location: foro.php");
        exit();
    }
}

// --- 4. ELIMINAR PUBLICACIÓN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_publicacion'])) {
    $id_pub_eliminar = intval($_POST['eliminar_publicacion_id']);
    // Validar que la publicación pertenezca al usuario
    $query_valida = $conexion->prepare("SELECT id_usuaria FROM publicaciones WHERE id = ?");
    $query_valida->bind_param("i", $id_pub_eliminar);
    $query_valida->execute();
    $result_valida = $query_valida->get_result();
    $pub = $result_valida->fetch_assoc();
    $query_valida->close();

    if ($pub && $pub['id_usuaria'] == $id_usuaria) {
        // Eliminar publicación
        $stmt_del = $conexion->prepare("DELETE FROM publicaciones WHERE id = ?");
        $stmt_del->bind_param("i", $id_pub_eliminar);
        $stmt_del->execute();
        $stmt_del->close();

        // Eliminar reacciones asociadas
        $stmt_del_reac = $conexion->prepare("DELETE FROM reacciones WHERE id_publicacion = ?");
        $stmt_del_reac->bind_param("i", $id_pub_eliminar);
        $stmt_del_reac->execute();
        $stmt_del_reac->close();

        // Eliminar comentarios asociados
        $stmt_del_com = $conexion->prepare("DELETE FROM comentarios WHERE id_publicacion = ?");
        $stmt_del_com->bind_param("i", $id_pub_eliminar);
        $stmt_del_com->execute();
        $stmt_del_com->close();
    }

    header("Location: foro.php");
    exit();
}

// --- 5. AGREGAR COMENTARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_respuesta'])) {
    $id_publicacion_com = intval($_POST['id_publicacion_comentario']);
    $comentario = trim($_POST['comentario']);
    if ($comentario !== '') {
        $stmt_com = $conexion->prepare("INSERT INTO comentarios (id_publicacion, id_usuaria, comentario, fecha_comentario) VALUES (?, ?, ?, NOW())");
        $stmt_com->bind_param("iis", $id_publicacion_com, $id_usuaria, $comentario);
        $stmt_com->execute();
        $stmt_com->close();
        header("Location: foro.php");
        exit();
    }
}

// --- 6. AGREGAR O ACTUALIZAR REACCIÓN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reaccion_tipo'])) {
    $id_publicacion_reaccion = intval($_POST['id_publicacion']);
    $tipo_reaccion = $_POST['reaccion_tipo'];

    // Verificar si ya reaccionó
    $query_reac = $conexion->prepare("SELECT id FROM reacciones WHERE id_publicacion = ? AND id_usuaria = ?");
    $query_reac->bind_param("ii", $id_publicacion_reaccion, $id_usuaria);
    $query_reac->execute();
    $result_reac = $query_reac->get_result();

    if ($result_reac->num_rows > 0) {
        // Actualizar tipo de reacción
        $stmt_upd = $conexion->prepare("UPDATE reacciones SET tipo = ?, fecha_reaccion = NOW() WHERE id_publicacion = ? AND id_usuaria = ?");
        $stmt_upd->bind_param("sii", $tipo_reaccion, $id_publicacion_reaccion, $id_usuaria);
        $stmt_upd->execute();
        $stmt_upd->close();
    } else {
        // Insertar nueva reacción
        $stmt_ins = $conexion->prepare("INSERT INTO reacciones (id_publicacion, id_usuaria, tipo) VALUES (?, ?, ?)");
        $stmt_ins->bind_param("iis", $id_publicacion_reaccion, $id_usuaria, $tipo_reaccion);
        $stmt_ins->execute();
        $stmt_ins->close();
    }
    header("Location: foro.php");
    exit();
}

// --- 7. DETERMINAR ORDEN DE PUBLICACIONES ---
$orden = "p.fecha_publicacion DESC"; // orden por defecto
if (isset($_GET['orden']) && $_GET['orden'] === 'populares') {
    $orden = "reac.total_reacciones DESC, p.fecha_publicacion DESC";
}

// --- 8. CONSULTA PUBLICACIONES ---
$query = "
SELECT p.id, p.id_usuaria, p.contenido, p.imagen, p.fecha_publicacion, u.nombre, u.foto,
       IFNULL(reac.total_reacciones, 0) AS total_reacciones,
       IFNULL(com.total_comentarios, 0) AS total_comentarios
FROM publicaciones p
JOIN usuarias u ON p.id_usuaria = u.id
LEFT JOIN (
    SELECT id_publicacion, COUNT(*) AS total_reacciones
    FROM reacciones
    GROUP BY id_publicacion
) reac ON p.id = reac.id_publicacion
LEFT JOIN (
    SELECT id_publicacion, COUNT(*) AS total_comentarios
    FROM comentarios
    GROUP BY id_publicacion
) com ON p.id = com.id_publicacion
ORDER BY $orden
";

$resultado = mysqli_query($conexion, $query);

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Foro SOPCare</title>
<link rel="stylesheet" href="css/foro.css">
<script>
function reaccionar(idPublicacion, tipo) {
    const formData = new FormData();
    formData.append('id_publicacion', idPublicacion);
    formData.append('reaccion_tipo', tipo);

    fetch('foro.php', {
        method: 'POST',
        body: formData
    }).then(() => {
        location.reload();
    });
}
</script>
</head>
<body>

<header>
    <h1>Bienvenida al Foro SOPCare</h1>
    <?php
    $fotoSesion = !empty($_SESSION['usuario']['foto']) ? 'fotos/' . $_SESSION['usuario']['foto'] : 'imagenes/usuarias.png';
    ?>
    <div id="usuario-info">
    <img src="<?php echo htmlspecialchars($fotoSesion); ?>" alt="Foto de perfil" width="50" height="50" style="border-radius:50%; object-fit:cover;">
    <span><?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></span>
</div>
    <a href="principal.php" class="btn-dashboard">Volver al menú</a>

    <form method="GET" style="margin-top:10px;">
        <label for="orden">Ordenar por:</label>
        <select name="orden" id="orden" onchange="this.form.submit()">
            <option value="recientes" <?php if(!isset($_GET['orden']) || $_GET['orden'] === 'recientes') echo 'selected'; ?>>Más recientes</option>
            <option value="populares" <?php if(isset($_GET['orden']) && $_GET['orden'] === 'populares') echo 'selected'; ?>>Más populares</option>
        </select>
    </form>
</header>

<main>

<section id="crear-publicacion">
    <form method="POST" enctype="multipart/form-data" id="formPublicacion">
        <textarea name="contenido" placeholder="¿En qué estás pensando?" required></textarea>
        <input type="file" name="imagen" accept="image/*">
        <button type="submit" name="nueva_publicacion">Publicar</button>
    </form>
</section>

<section id="publicaciones">
<?php while ($p = mysqli_fetch_assoc($resultado)) { ?>
    <div class="publicacion">
        <div class="publicacion-header">
            <?php
$fotoPerfil = !empty($p['foto']) ? 'fotos/' . $p['foto'] : 'imagenes/usuarias.png';
?>
<img src="<?php echo htmlspecialchars($fotoPerfil); ?>" width="50" height="50" alt="Foto perfil" style="border-radius:50%; object-fit:cover;">

            <strong><?php echo htmlspecialchars($p['nombre']); ?></strong>
            <span><?php echo date('d/m/Y H:i', strtotime($p['fecha_publicacion'])); ?></span>

            <?php if ($p['id_usuaria'] == $id_usuaria): ?>
                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que quieres eliminar esta publicación?');">
                    <input type="hidden" name="eliminar_publicacion_id" value="<?php echo $p['id']; ?>">
                    <button type="submit" name="eliminar_publicacion">Eliminar</button>
                </form>

                <form method="POST" style="display:inline;">
                    <input type="hidden" name="editar_publicacion_id" value="<?php echo $p['id']; ?>">
                    <button type="submit" name="editar_publicacion">Editar</button>
                </form>
            <?php endif; ?>
        </div>

        <p><?php echo nl2br(htmlspecialchars($p['contenido'])); ?></p>
        <?php if (!empty($p['imagen'])): ?>
            <img src="<?php echo htmlspecialchars($p['imagen']); ?>" class="imagen-publicacion" alt="Imagen publicación">
        <?php endif; ?>

        <div class="reacciones" style="margin-bottom: 10px;">
            <button type="button" onclick="reaccionar(<?php echo $p['id']; ?>, 'me_gusta')">👍 Me gusta</button>
            <span><?php echo $p['total_reacciones']; ?> reacciones</span>
        </div>

        <div class="comentarios">
            <strong>Comentarios (<?php echo $p['total_comentarios']; ?>):</strong>
            <?php
            $id_pub = $p['id'];
            $query_comentarios = "SELECT c.comentario, c.fecha_comentario, u.nombre 
                                  FROM comentarios c 
                                  JOIN usuarias u ON c.id_usuaria = u.id 
                                  WHERE c.id_publicacion = $id_pub 
                                  ORDER BY c.fecha_comentario ASC";
            $comentarios = mysqli_query($conexion, $query_comentarios);
            while ($c = mysqli_fetch_assoc($comentarios)) {
                echo "<div class='comentario'>
                        <strong>" . htmlspecialchars($c['nombre']) . "</strong>
                        <span>" . date('d/m/Y H:i', strtotime($c['fecha_comentario'])) . "</span>
                        <p>" . nl2br(htmlspecialchars($c['comentario'])) . "</p>
                      </div>";
            }
            ?>

            <form method="POST" class="formRespuesta">
                <input type="hidden" name="id_publicacion_comentario" value="<?php echo $p['id']; ?>">
                <textarea name="comentario" placeholder="Escribe un comentario..." required></textarea>
                <button type="submit" name="nueva_respuesta">Comentar</button>
            </form>
        </div>
    </div>
<?php } ?>
</section>

<!-- Formulario para editar publicación, solo si se activa la edición -->
<?php if (isset($editar_publicacion_id) && $editar_publicacion_id !== null): ?>
<section id="editar-publicacion">
  <h2>Editar publicación</h2>
  <div class="editar-publicacion-form">
    <form action="foro.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id_publicacion_editar" value="<?php echo $editar_publicacion_id; ?>" />

      <label for="contenidoEditar">Editar contenido</label>
      <textarea id="contenidoEditar" name="contenido_editado" rows="4" required><?php echo htmlspecialchars($editar_contenido); ?></textarea>

      <p class="imagen-actual">Imagen actual:</p>
      <?php if ($editar_imagen): ?>
        <img src="<?php echo htmlspecialchars($editar_imagen); ?>" alt="Imagen actual" class="imagen-publicacion" /><br/>
      <?php else: ?>
        <span>No hay imagen.</span><br/>
      <?php endif; ?>

      <label for="imagenEditar">Cambiar imagen (opcional):</label>
      <input type="file" id="imagenEditar" name="imagen_editada" accept="image/*" />

      <div class="botones-editar">
        <button type="submit" name="guardar_cambios">Guardar cambios</button>
        <a href="foro.php" class="cancelar">Cancelar</a>
      </div>
    </form>
  </div>
</section>
<?php endif; ?>

</main>

<footer>
    <p>&copy; 2025 SOPCare</p>
</footer>

</body>
</html>
