document.addEventListener("DOMContentLoaded", function () {
    const medicacionSi = document.getElementById("toma_medicacion_si");
    const medicacionOpciones = document.getElementById("medicacion_opciones");

    const dietaSi = document.getElementById("sigue_dieta_si");
    const tipoDietaOpciones = document.getElementById("tipo_dieta_opciones");

    // Mostrar u ocultar según estado inicial
    if (medicacionSi && medicacionOpciones) {
        medicacionOpciones.style.display = medicacionSi.checked ? "block" : "none";

        // Añadir eventos de cambio
        document.querySelectorAll("input[name='toma_medicacion']").forEach(function (radio) {
            radio.addEventListener("change", function () {
                medicacionOpciones.style.display = this.value === "si" ? "block" : "none";
            });
        });
    }

    if (dietaSi && tipoDietaOpciones) {
        tipoDietaOpciones.style.display = dietaSi.checked ? "block" : "none";

        // Añadir eventos de cambio
        document.querySelectorAll("input[name='sigue_dieta']").forEach(function (radio) {
            radio.addEventListener("change", function () {
                tipoDietaOpciones.style.display = this.value === "si" ? "block" : "none";
            });
        });
    }
});
