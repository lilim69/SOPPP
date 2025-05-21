<?php
session_start();
include 'php/conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: iniciosesion.php");
    exit();
}

$id_usuaria = $_SESSION['usuario']['id'];

// Obtener datos de la usuaria logueada
$query = "SELECT * FROM usuarias WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_usuaria);
$stmt->execute();
$resultado = $stmt->get_result();
$usuaria = $resultado->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil - SOPCare</title>
    <link rel="stylesheet" href="css/perfil.css">
    <script>
    function toggleMed() {
        document.getElementById("medicacion_opciones").style.display =
            document.getElementById("toma_medicacion_si").checked ? "block" : "none";
    }

    function toggleDieta() {
        document.getElementById("tipo_dieta_opciones").style.display =
            document.getElementById("sigue_dieta_si").checked ? "block" : "none";
    }

    window.onload = function() {
        toggleMed();
        toggleDieta();
    }
    </script>
</head>
<body>
    <div class="perfil-container">
        <h2>Editar Perfil</h2>
        <form action="guardar_perfil.php" method="POST" enctype="multipart/form-data">
        <label>Nombre completo*:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($usuaria['nombre']) ?>" required><br>

        <label>Edad*:</label>
        <input type="number" name="edad" value="<?= htmlspecialchars($usuaria['edad']) ?>" required><br>

        <label>Peso (kg):</label>
        <input type="number" step="0.01" name="peso" value="<?= htmlspecialchars($usuaria['peso']) ?>">
        <small>Opcional, pero ayuda a personalizar tu experiencia</small><br>

        <label>Altura (cm):</label>
        <input type="number" step="0.01" name="altura" value="<?= htmlspecialchars($usuaria['altura']) ?>">
        <small>Opcional</small><br>

        <label>Fecha de diagnóstico:</label>
        <input type="date" name="fecha_diagnostico" value="<?= htmlspecialchars($usuaria['fecha_diagnostico']) ?>"><br>

        <label>Foto de perfil:</label>
        <input type="file" name="foto"><br>
        <?php if (!empty($usuaria['foto'])): ?>
            <img src="fotos/<?= htmlspecialchars($usuaria['foto']) ?>" width="100"><br>
        <?php endif; ?>

        <label>¿Diagnóstico confirmado de SOP?</label><br>
        <input type="radio" name="diagnostico_confirmado" value="si" <?= $usuaria['diagnostico_confirmado'] == 'si' ? 'checked' : '' ?>> Sí
        <input type="radio" name="diagnostico_confirmado" value="no" <?= $usuaria['diagnostico_confirmado'] == 'no' ? 'checked' : '' ?>> No<br>

        <label>Tipo de SOP:</label>
        <select name="tipo_sop">
            <option value="">Seleccione</option>
            <?php
            $tipos_sop = ['Resistencia a la insulina', 'Inflamatorio', 'Post-píldora', 'Adrenérgico'];
            foreach ($tipos_sop as $tipo) {
                $selected = ($usuaria['tipo_sop'] == $tipo) ? 'selected' : '';
                echo "<option value=\"$tipo\" $selected>$tipo</option>";
            }
            ?>
        </select>
        <small>Opcional</small><br>

        <label>¿Menstruación irregular?</label><br>
        <input type="radio" name="menstruacion_irregular" value="si" <?= $usuaria['menstruacion_irregular'] == 'si' ? 'checked' : '' ?>> Sí
        <input type="radio" name="menstruacion_irregular" value="no" <?= $usuaria['menstruacion_irregular'] == 'no' ? 'checked' : '' ?>> No<br>

        <label>¿Toma medicación?</label><br>
        <input type="radio" id="toma_medicacion_si" name="toma_medicacion" value="si" <?= $usuaria['toma_medicacion'] == 'si' ? 'checked' : '' ?> onclick="toggleMed()"> Sí
        <input type="radio" name="toma_medicacion" value="no" <?= $usuaria['toma_medicacion'] == 'no' ? 'checked' : '' ?> onclick="toggleMed()"> No<br>

        <div id="medicacion_opciones" style="display: <?= $usuaria['toma_medicacion'] == 'si' ? 'block' : 'none' ?>;">
    <label>Medicación actual (selección múltiple):</label><br>
    <?php
    $suplementos = ['Myoinositol', 'Inositol', 'Omega-3', 'Magnesio', 'Zinc', 'Berberina'];
    $medicacion_actual = isset($usuaria['medicacion']) ? array_map('trim', explode(',', $usuaria['medicacion'])) : [];
    foreach ($suplementos as $sup) {
        $checked = in_array($sup, $medicacion_actual) ? 'checked' : '';
        echo "<label><input type=\"checkbox\" name=\"medicacion[]\" value=\"$sup\" $checked> $sup</label><br>";
    }
    ?>
    <label>Otro:</label>
    <input type="text" name="medicacion_otro" value="<?= htmlspecialchars($usuaria['medicacion_otro'] ?? '') ?>"><br>
</div>


        <label>Objetivos personales:</label><br>
        <input type="text" name="objetivos" value="<?= htmlspecialchars($usuaria['objetivos']) ?>">
        <small>Opcional. Ej: Bajar peso, regular ciclo, mejorar piel...</small><br>

        <label>Nivel de actividad física:</label>
        <select name="nivel_actividad">
            <option value="">Seleccione</option>
            <?php
            $niveles = ['Bajo', 'Moderado', 'Alto'];
            foreach ($niveles as $nivel) {
                $selected = ($usuaria['nivel_actividad'] == $nivel) ? 'selected' : '';
                echo "<option value=\"$nivel\" $selected>$nivel</option>";
            }
            ?>
        </select><br>

        <label>¿Sigue una dieta específica?</label><br>
        <input type="radio" id="sigue_dieta_si" name="sigue_dieta" value="si" <?= $usuaria['sigue_dieta'] == 'si' ? 'checked' : '' ?> onclick="toggleDieta()"> Sí
        <input type="radio" name="sigue_dieta" value="no" <?= $usuaria['sigue_dieta'] == 'no' ? 'checked' : '' ?> onclick="toggleDieta()"> No<br>

        <div id="tipo_dieta_opciones" style="display: <?= $usuaria['sigue_dieta'] == 'si' ? 'block' : 'none' ?>;">
            <label>Tipo de dieta:</label>
            <select name="tipo_dieta">
                <option value="">Seleccione</option>
                <?php
                $dietas = ['Keto', 'Baja en carbohidratos', 'Vegetariana', 'Otro'];
                foreach ($dietas as $dieta) {
                    $selected = ($usuaria['tipo_dieta'] == $dieta) ? 'selected' : '';
                    echo "<option value=\"$dieta\" $selected>$dieta</option>";
                }
                ?>
            </select><br>

            <label>Especificar otro (si aplica):</label>
            <input type="text" name="tipo_dieta_otro" value="<?= htmlspecialchars($usuaria['tipo_dieta_otro']) ?>"><br>
        </div>

        <button type="submit">Guardar cambios</button>
        <a href="principal.php" class="boton-dashboard">Volver al menú</a>
    </form>
              </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: '¡Perfil actualizado!',
        text: 'Tus datos se han guardado correctamente.',
        confirmButtonText: 'Aceptar'
    });
});
</script>
<?php endif; ?>

</body>
</html>
