// Mostrar nombre de usuaria desde localStorage
window.onload = function () {
  const nombre = localStorage.getItem("nombreUsuaria") || "SOPStar";
  document.getElementById("bienvenida").textContent = `¡Bienvenida, ${nombre}!`;
};

// Redirigir a los módulos
function irAModulo(modulo) {
  switch (modulo) {
    case 'sintomas':
      window.location.href = 'sintomas.html';
      break;
    case 'ciclo':
      window.location.href = 'ciclo.html';
      break;
    case 'suplementos':
      window.location.href = 'suplementos.html';
      break;
    case 'foro':
      window.location.href = 'foro.html';
      break;
    case 'recetas':
      window.location.href = 'recetas.html';
      break;
  }
}

// Cerrar sesión
function cerrarSesion() {
  localStorage.clear();
  window.location.href = 'iniciosesion.html';
}
