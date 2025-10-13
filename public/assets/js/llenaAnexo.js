function initAnexoAutocomplete() {
    console.log('Inicializando autocomplete para anexos...');
    
    const selectAnexo = document.getElementById('anexo');
    const descTextarea = document.getElementById('DescripcionCitologica');
    
    if (!selectAnexo || !descTextarea) {
        console.log('Elementos de anexo no encontrados, reintentando en 100ms...');
        setTimeout(initAnexoAutocomplete, 100);
        return;
    }
    
    selectAnexo.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option && option.value && option.value !== '') {
            const anexoTexto = option.getAttribute('data-anexo') || option.textContent;
            
            if (anexoTexto && anexoTexto.trim() !== '') {
                agregarAnexoAlTextarea(descTextarea, anexoTexto.trim());
            }
        }
        
        // Resetear el select
        this.value = '';
    });
    
    console.log('Autocomplete de anexos inicializado correctamente');
}

function agregarAnexoAlTextarea(textarea, nuevoAnexo) {
    const contenidoActual = textarea.value.trim();
    
    // Limpiar el nuevo anexo (remover punto final si existe)
    const anexoLimpio = nuevoAnexo.replace(/\.$/, '');
    
    if (contenidoActual === '') {
        // Si el textarea está vacío, agregar el anexo con punto
        textarea.value = anexoLimpio + '.';
    } else {
        // Si ya hay contenido
        const contenidoLimpio = contenidoActual.replace(/\.$/, '');
        
        // Verificar si el contenido actual termina con algún signo de puntuación
        const ultimoCaracter = contenidoLimpio.slice(-1);
        const tienePuntuacion = /[.,;:]$/.test(ultimoCaracter);
        
        if (tienePuntuacion) {
            // Si termina con puntuación, agregar espacio y el anexo con punto
            textarea.value = contenidoLimpio + ' ' + anexoLimpio + '.';
        } else {
            // Si no termina con puntuación, agregar punto, espacio y el anexo con punto
            textarea.value = contenidoLimpio + '. ' + anexoLimpio + '.';
        }
    }
    
    // Hacer scroll al final
    textarea.scrollTop = textarea.scrollHeight;
    
    console.log('Anexo agregado:', anexoLimpio);
}

document.addEventListener('DOMContentLoaded', initAnexoAutocomplete);