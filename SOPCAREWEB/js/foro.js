document.addEventListener('DOMContentLoaded', () => {
  const formPublicacion = document.getElementById('formPublicacion');
  const formRespuestas = document.querySelectorAll('.formRespuesta');

  // Div para mostrar errores (puedes añadir este div en el HTML, por ejemplo en #crear-publicacion)
  function mostrarError(form, mensaje) {
    let errorDiv = form.querySelector('.error-message');
    if (!errorDiv) {
      errorDiv = document.createElement('div');
      errorDiv.className = 'error-message';
      errorDiv.style.color = 'red';
      errorDiv.style.marginTop = '5px';
      form.prepend(errorDiv);
    }
    errorDiv.textContent = mensaje;
  }

  // Limpia errores
  function limpiarError(form) {
    const errorDiv = form.querySelector('.error-message');
    if (errorDiv) errorDiv.textContent = '';
  }

  // Validación al enviar nueva publicación
  if (formPublicacion) {
    formPublicacion.addEventListener('submit', (e) => {
      limpiarError(formPublicacion);
      const contenido = formPublicacion.querySelector('textarea[name="contenido"]').value.trim();
      const imagen = formPublicacion.querySelector('input[type="file"]').files[0];

      if (contenido === '') {
        e.preventDefault();
        mostrarError(formPublicacion, 'Por favor escribe el contenido de tu publicación.');
        return;
      }

      // Validar tipo y tamaño de imagen si se seleccionó
      if (imagen) {
        const tiposValidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const maxSizeMB = 5;
        if (!tiposValidos.includes(imagen.type)) {
          e.preventDefault();
          mostrarError(formPublicacion, 'La imagen debe ser JPG, PNG, GIF o WEBP.');
          return;
        }
        if (imagen.size > maxSizeMB * 1024 * 1024) {
          e.preventDefault();
          mostrarError(formPublicacion, `La imagen no puede superar los ${maxSizeMB} MB.`);
          return;
        }
      }

      // Opcional: deshabilitar botón para evitar múltiples envíos
      const btnPublicar = formPublicacion.querySelector('button[type="submit"]');
      btnPublicar.disabled = true;
      btnPublicar.textContent = 'Publicando...';
    });
  }

  // Validación de comentarios en cada formulario de respuesta
  formRespuestas.forEach(form => {
    form.addEventListener('submit', (e) => {
      limpiarError(form);
      const textarea = form.querySelector('textarea');
      if (textarea.value.trim() === '') {
        e.preventDefault();
        mostrarError(form, 'La respuesta no puede estar vacía.');
        textarea.focus();
      } else {
        // Opcional: deshabilitar botón mientras se envía
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Enviando...';
      }
    });
  });

  // Aquí puedes añadir más mejoras visuales o interactivas si deseas
});
