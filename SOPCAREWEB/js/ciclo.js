const calendario = document.getElementById('calendario');
const nombreMes = document.getElementById('nombre-mes');
const btnPrev = document.getElementById('btnPrev');
const btnNext = document.getElementById('btnNext');

let fechaActual = new Date();

// NO redeclarar datosGuardados acá porque viene desde PHP:
// datosGuardados ya está disponible globalmente desde el PHP

const flujoIconos = {
  'ninguno': '',
  'leve': '💧',
  'moderado': '💧💧',
  'abundante': '💧💧💧'
};

const ovulacionIcono = '🌸';

function calcularDiasOvulacion() {
  let primerDiaMenstruacion = null;
  for (const fecha of Object.keys(datosGuardados)) {
    if (datosGuardados[fecha] !== 'ninguno') {
      if (!primerDiaMenstruacion || fecha < primerDiaMenstruacion) {
        primerDiaMenstruacion = fecha;
      }
    }
  }
  if (!primerDiaMenstruacion) return [];

  const baseDate = new Date(primerDiaMenstruacion);
  let diasOvulacion = [];
  for (let i = 14; i <= 16; i++) { // ventana fértil
    const ovDate = new Date(baseDate);
    ovDate.setDate(baseDate.getDate() + i);
    diasOvulacion.push(ovDate.toISOString().slice(0, 10));
  }
  return diasOvulacion;
}

function dibujarCalendario(mes, año) {
  calendario.innerHTML = '';

  const primerDia = new Date(año, mes, 1);
  const ultimoDia = new Date(año, mes + 1, 0);
  nombreMes.textContent = primerDia.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });

  const diaSemanaInicio = primerDia.getDay();

  for (let i = 0; i < diaSemanaInicio; i++) {
    const celdaVacia = document.createElement('div');
    celdaVacia.classList.add('dia', 'vacío');
    calendario.appendChild(celdaVacia);
  }

  const fechasMes = [];
  for (let d = 1; d <= ultimoDia.getDate(); d++) {
    const fechaStr = `${año}-${String(mes + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    fechasMes.push(fechaStr);
  }

  const diasOvulacion = calcularDiasOvulacion();

  fechasMes.forEach(fecha => {
    const dia = parseInt(fecha.slice(-2), 10);
    const flujo = datosGuardados[fecha] || 'ninguno';

    const celda = document.createElement('div');
    celda.classList.add('dia');
    celda.dataset.fecha = fecha;
    celda.innerHTML = `<span class="numero">${dia}</span>`;

    if (flujo !== 'ninguno') {
      const gotas = document.createElement('div');
      gotas.classList.add('gotas');
      gotas.textContent = flujoIconos[flujo];
      celda.appendChild(gotas);
    }

    if (diasOvulacion.includes(fecha)) {
      const ovIcon = document.createElement('div');
      ovIcon.classList.add('ovulacion');
      ovIcon.textContent = ovulacionIcono;
      celda.appendChild(ovIcon);
    }

    celda.addEventListener('click', () => {
      Swal.fire({
        title: `Flujo menstrual para ${fecha}`,
        input: 'select',
        inputOptions: {
          'ninguno': 'Ninguno',
          'leve': 'Leve',
          'moderado': 'Moderado',
          'abundante': 'Abundante'
        },
        inputValue: flujo,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
          if (!value) return 'Por favor selecciona una opción';
        }
      }).then(result => {
        if (result.isConfirmed) {
          guardarFlujo(fecha, result.value);
        }
      });
    });

    calendario.appendChild(celda);
  });
}

function guardarFlujo(fecha, flujo) {
  fetch('ciclo.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ fecha: fecha, flujo: flujo })
  })
  .then(res => res.json())
  .then(data => {
    if (data.error) {
      Swal.fire('Error', data.error, 'error');
    } else {
      if (flujo === 'ninguno') {
        delete datosGuardados[fecha];
      } else {
        datosGuardados[fecha] = flujo;
      }
      Swal.fire('¡Listo!', data.mensaje, 'success');
      dibujarCalendario(fechaActual.getMonth(), fechaActual.getFullYear());
    }
  })
  .catch(() => {
    Swal.fire('Error', 'No se pudo guardar el dato.', 'error');
  });
}

btnPrev.addEventListener('click', () => {
  fechaActual.setMonth(fechaActual.getMonth() - 1);
  dibujarCalendario(fechaActual.getMonth(), fechaActual.getFullYear());
});

btnNext.addEventListener('click', () => {
  fechaActual.setMonth(fechaActual.getMonth() + 1);
  dibujarCalendario(fechaActual.getMonth(), fechaActual.getFullYear());
});

dibujarCalendario(fechaActual.getMonth(), fechaActual.getFullYear());
