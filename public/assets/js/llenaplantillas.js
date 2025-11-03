document.addEventListener('DOMContentLoaded', function() {
    // Configuración para autocompletar
    const configuraciones = [
        { select: 'descripcion_macroscopica', textarea: 'descripcionMacroscopica' },
        { select: 'descripcion_microscopica', textarea: 'descripcionMicroscopica' },
        { select: 'diagnostico_microscopico', textarea: 'diagnosticoMicroscopico' }
    ];
    
    configuraciones.forEach(config => {
        const selectElement = document.getElementById(config.select);
        const textareaElement = document.getElementById(config.textarea);
        
        if (selectElement && textareaElement) {
            selectElement.addEventListener('change', function() {
                textareaElement.value = this.value;
            });
        }
    });
});