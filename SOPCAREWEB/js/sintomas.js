async function guardarSintomas() {
  const fechaInput = document.getElementById('fecha');
  const fecha = fechaInput.value;
  const otros = document.getElementById('otros').value.trim();

  if (!fecha) {
    Swal.fire('Error', 'Por favor selecciona una fecha.', 'error');
    return;
  }

  // Obtener síntomas seleccionados
  const checkboxes = document.querySelectorAll('#grupo-sintomas input[type="checkbox"]');
  let sintomasSeleccionados = [];
  checkboxes.forEach(chk => {
    if (chk.checked) {
      sintomasSeleccionados.push(chk.value);
    }
  });

  if (sintomasSeleccionados.length === 0 && otros === '') {
    Swal.fire('Error', 'Selecciona al menos un síntoma o escribe uno en "Otros".', 'error');
    return;
  }

  try {
    const response = await fetch('guardar_sintomas.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        fecha: fecha,
        sintomas: sintomasSeleccionados,
        otros: otros
      })
    });

    const data = await response.json();

    if (data.success) {
      Swal.fire('Guardado', 'Síntomas registrados correctamente.', 'success');
      fechaInput.value = '';
      document.getElementById('otros').value = '';
      checkboxes.forEach(chk => chk.checked = false);
      cargarHistorial();
    } else {
      Swal.fire('Error', data.error || 'No se pudo guardar.');
    }
  } catch (error) {
    console.error('Fetch error:', error);
    Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
  }
}

async function cargarHistorial() {
  try {
    const response = await fetch('obtener_sintomas.php');
    if (!response.ok) throw new Error('Error al obtener historial');

    const data = await response.json();

    const lista = document.getElementById('lista-sintomas');
    lista.innerHTML = '';

    if (!data.length) {
      lista.innerHTML = '<li>No hay registros.</li>';
      return;
    }

    data.forEach(item => {
      const sintomas = JSON.parse(item.sintomas);
      const sintomasTexto = sintomas.join(', ');
      const fechaFormateada = new Date(item.fecha).toLocaleDateString();
      const li = document.createElement('li');
      li.textContent = `${fechaFormateada}: ${sintomasTexto}`;
      lista.appendChild(li);
    });
  } catch (error) {
    console.error('Error al cargar historial:', error);
  }
}

window.onload = () => {
  cargarHistorial();
};
