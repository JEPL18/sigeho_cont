// assets/js/main.js

document.addEventListener("DOMContentLoaded", function() {
    
    // ==========================================
    // VARIABLES GENERALES
    // ==========================================
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    // ==========================================
    // 1. LÓGICA PARA MÓVILES (Botón de 3 líneas)
    // ==========================================
    const btnMenuMovil = document.getElementById('menu-toggle');
    
    if (btnMenuMovil && sidebar) {
        btnMenuMovil.addEventListener('click', function() {
            sidebar.classList.toggle('mostrar-movil');
        });

        // Cerrar el menú si hacen clic fuera de él en el celular
        document.addEventListener('click', function(event) {
            const isClickInside = sidebar.contains(event.target) || btnMenuMovil.contains(event.target);
            if (!isClickInside && sidebar.classList.contains('mostrar-movil')) {
                sidebar.classList.remove('mostrar-movil');
            }
        });
    }

    // ==========================================
    // 2. LÓGICA PARA ESCRITORIO (Colapsar/Expandir)
    // ==========================================
    const btnToggleDesktop = document.getElementById('toggleSidebar');
    
    if (btnToggleDesktop && sidebar && mainContent) {
        btnToggleDesktop.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Recargar el tamaño de elementos (como tablas o calendarios) al cambiar el ancho
            setTimeout(() => { window.dispatchEvent(new Event('resize')); }, 300);
        });
    }

    // ==========================================
    // 3. BUSCADOR EN TIEMPO REAL PARA LAS TABLAS
    // ==========================================
    const buscador = document.getElementById('buscadorGeneral');
    
    if (buscador) {
        buscador.addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let filas = document.querySelectorAll('.card-body table tbody tr');

            filas.forEach(function(fila) {
                let textoFila = fila.textContent.toLowerCase();
                if (textoFila.includes(filtro)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }

    // ==========================================
    // 4. PREVENCIÓN DE ERRORES: VALIDACIONES EN TIEMPO REAL
    // ==========================================
    
    // A. Validar campos de solo letras
    const inputsLetras = document.querySelectorAll('.solo-letras');
    inputsLetras.forEach(function(input) {
        input.addEventListener('keypress', function(e) {
            // Permitir solo letras, espacios y el punto
            const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.]$/;
            if (!regex.test(e.key)) {
                e.preventDefault();
            }
        });
    });

    // B. Validar campos de solo números (Cédulas, Capacidad, etc.)
    const inputsNumeros = document.querySelectorAll('.solo-numeros');
    inputsNumeros.forEach(function(input) {
        input.addEventListener('keypress', function(e) {
            // Permitir exclusivamente números del 0 al 9
            const regex = /^[0-9]$/;
            if (!regex.test(e.key)) {
                e.preventDefault();
            }
        });
    });

});