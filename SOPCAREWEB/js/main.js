function irA(opcion) {
    switch (opcion) {
        case 'iniciosesion':
            window.location.href = 'iniciosesion.html';
            break;
        case 'registro':
            window.location.href = 'registro.html';
            break;
        default:
            console.error("Opción no reconocida:", opcion);
    }
}
