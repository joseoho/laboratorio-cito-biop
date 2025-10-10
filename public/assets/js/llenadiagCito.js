async function cargarDiagcito() {
    try {
        // Hacer la solicitud al endpoint
        const response = await fetch('http://localhost:8000/api/diagcito');
        const diagcitos = await response.json();

        // Guardar los datos en una variable global para usarlos después
        window.diagcitosData = diagcitos;

        // Obtener el select
        const selectDiagcitos = document.getElementById('cod');

        // Limpiar el select (opcional)
        selectDiagcitos.innerHTML = '<option value="">Seleccione</option>';

        // Llenar el select con los datos
        diagcitos.forEach(diagcito => {
            const option = document.createElement('option');
            option.value = diagcito.Cod; // Valor que se enviará al backend
            option.textContent = diagcito.Cod; // Texto que se mostrará en el select
            selectDiagcitos.appendChild(option);
        });

        // Agregar event listener para cuando cambie la selección
        selectDiagcitos.addEventListener('change', function() {
            const codSeleccionado = this.value;
            if (!codSeleccionado) {
                // Si se selecciona la opción vacía, limpiar los textareas
                document.getElementById('descripcionCitologia').value = '';
                document.getElementById('diagnosticoCitologia').value = '';
                return;
            }

            // Buscar el diagcito correspondiente al código seleccionado
            const diagcitoSeleccionado = window.diagcitosData.find(d => d.Cod === codSeleccionado);

            if (diagcitoSeleccionado) {
                // Llenar los textareas con los datos correspondientes
                document.getElementById('descripcionCitologia').value = diagcitoSeleccionado.Descripcion || '';
                document.getElementById('diagnosticoCitologia').value = diagcitoSeleccionado.Diagnostico || '';
            }
        });
    } catch (error) {
        console.error('Error al cargar los datos:', error);
    }
}

// Llamar a la función cuando la página cargue
document.addEventListener('DOMContentLoaded', cargarDiagcito);