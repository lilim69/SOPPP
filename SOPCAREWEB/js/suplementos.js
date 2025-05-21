function guardarSintomas() {
  const fecha = document.getElementById('fecha').value;
  const otros = document.getElementById('otros').value;
  const checks = document.querySelectorAll('#grupo-sintomas input[type="checkbox"]:checked');

  if (!fecha || checks.length === 0) {
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: '¡Debes seleccionar al menos un síntoma y una fecha!',
    });
    return;
  }

  const sintomasSeleccionados = Array.from(checks).map(cb => cb.value);
  if (otros.trim() !== "") {
    sintomasSeleccionados.push("Otros: " + otros.trim());
  }

  fetch('guardar_sintomas.php', {  // <-- Aquí la ruta corregida, sin "php/"
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      fecha: fecha,
      sintomas: sintomasSeleccionados
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: '¡Síntomas registrados!',
        showConfirmButton: false,
        timer: 1500
      });
      // Limpiar campos
      document.getElementById('fecha').value = '';
      document.getElementById('otros').value = '';
      checks.forEach(cb => cb.checked = false);
      mostrarSintomas();
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.error || 'Ocurrió un error al guardar',
      });
    }
  })
  .catch(() => {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo conectar con el servidor',
    });
  });
}

// Función para cargar los síntomas desde la base de datos
function mostrarSintomas() {
  fetch('obtener_sintomas.php')  // <-- Ruta corregida aquí también
    .then(response => response.json())
    .then(data => {
      const lista = document.getElementById('lista-sintomas');
      lista.innerHTML = '';

      if (data.error) {
        lista.innerHTML = `<li>${data.error}</li>`;
        return;
      }

      data.forEach(item => {
        const sintomas = JSON.parse(item.sintomas);
        let sintomasTexto = sintomas.join(', ');
        if (item.otros && item.otros.trim() !== '') {
          sintomasTexto += ', Otros: ' + item.otros;
        }

        const li = document.createElement('li');
        li.classList.add('item-sintoma');
        li.innerHTML = `
          <div>
            <strong>${item.fecha}</strong><br>
            ${sintomasTexto}
          </div>
        `;
        lista.appendChild(li);
      });
    })
    .catch(() => {
      const lista = document.getElementById('lista-sintomas');
      lista.innerHTML = '<li>Error al cargar los síntomas.</li>';
    });
}

window.onload = mostrarSintomas;
