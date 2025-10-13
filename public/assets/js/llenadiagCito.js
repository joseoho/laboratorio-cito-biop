function initCitologiaAutocomplete() {
    const select = document.getElementById('cod');
    const desc = document.getElementById('DescripcionCitologica');
    const diag = document.getElementById('DiagnosticoCitologico');

    if (!select || !desc || !diag) {
        setTimeout(initCitologiaAutocomplete, 100);
        return;
    }

    select.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option && option.value) {
            desc.value = option.getAttribute('data-descripcion') || '';
            diag.value = option.getAttribute('data-diagnostico') || '';
        }
    });
}

document.addEventListener('DOMContentLoaded', initCitologiaAutocomplete);