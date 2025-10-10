// Función para cargar los médicos desde el endpoint
async function cargarMedicos() {
    try {
        // Hacer la solicitud al endpoint
        const response = await fetch('http://localhost:8000/api/medicos');
        const medicos = await response.json();

        // Obtener el select
        const selectMedicos = document.getElementById('medicos');

        // Limpiar el select (opcional)
        selectMedicos.innerHTML = '<option value="">Seleccione un médico</option>';

        // Llenar el select con los datos
        medicos.forEach(medico => {
            const option = document.createElement('option');
            option.value = medico.id; // Valor que se enviará al backend
            option.textContent = medico.Codigo; // Texto que se mostrará en el select
            selectMedicos.appendChild(option);
        });
    } catch (error) {
        console.error('Error al cargar los médicos:', error);
    }
}

// Llamar a la función cuando la página cargue
document.addEventListener('DOMContentLoaded', cargarMedicos);

// Función para cargar las procedencias desde el endpoint
async function cargarProcedencias() {
    try {
        // Hacer la solicitud al endpoint
        const response = await fetch('http://localhost:8000/api/procedencia');
        const procedencias = await response.json();

        // Obtener el select
        const selectProcedencia = document.getElementById('procedencia');

        // Limpiar el select (opcional)
        selectProcedencia.innerHTML = '<option value="">Seleccione una procedencia</option>';

        // Llenar el select con los datos
        procedencias.forEach(procedencia => {
            const option = document.createElement('option');
            option.value = procedencia.id; // Valor que se enviará al backend
            option.textContent = procedencia.Codigo; // Texto que se mostrará en el select
            selectProcedencia.appendChild(option);
        });
    } catch (error) {
        console.error('Error al cargar las procedencias:', error);
    }
}

// Llamar a la función cuando la página cargue
document.addEventListener('DOMContentLoaded', cargarProcedencias);

async function buscarPorNC() {
    const valor = document.getElementById('valorBusqueda').value; // Obtener el valor de búsqueda

    if (!valor) {
        alert('Por favor, ingrese un número de citología (NC).');
        return;
    }

    try {
        console.log('Realizando búsqueda por NC:', valor);

        // Hacer la solicitud al endpoint de NC
        const response = await fetch(`http://localhost:8000/api/pacientesnc/${valor}`);
        console.log('Respuesta del servidor:', response);

        if (!response.ok) {
            throw new Error(`Error en la respuesta: ${response.status} ${response.statusText}`);
        }

        const paciente = await response.json();
        console.log('Datos del paciente:', paciente);

        if (paciente) {
            // Llenar los campos del formulario con los datos del paciente
            llenarFormulario(paciente);
        } else {
            alert('No se encontró ningún paciente con el número de citología proporcionado.');
        }
    } catch (error) {
        console.error('Error al buscar por NC:', error);
        alert('Hubo un error al realizar la búsqueda por NC.');
    }
}


async function buscarPorNB() {
    const valor = document.getElementById('valorBusqueda').value; // Obtener el valor de búsqueda

    if (!valor) {
        alert('Por favor, ingrese un número de biopsia (NB).');
        return;
    }

    try {
        // Hacer la solicitud al endpoint de NB
        const response = await fetch(`http://localhost:8000/api/pacientesnb/${valor}`);
        const paciente = await response.json();

        if (paciente) {
            // Llenar los campos del formulario con los datos del paciente
            llenarFormulario(paciente);
        } else {
            alert('No se encontró ningún paciente con el número de biopsia proporcionado.');
        }
    } catch (error) {
        console.error('Error al buscar por NB:', error);
        alert('Hubo un error al realizar la búsqueda por NB.');
    }
}

////////////////////////busqueda por CI
async function buscarPorCI() {
    const valor = document.getElementById('valorBusqueda').value; // Obtener el valor de búsqueda

    if (!valor) {
        alert('Por favor, ingrese un número de biopsia (NB).');
        return;
    }

    try {
        // Hacer la solicitud al endpoint de NB
        const response = await fetch(`http://localhost:8000/api/pacientesci/${valor}`);
        const paciente = await response.json();

        if (paciente) {
            // Llenar los campos del formulario con los datos del paciente
            llenarFormulario(paciente);
        } else {
            alert('No se encontró ningún paciente con el número de biopsia proporcionado.');
        }
    } catch (error) {
        console.error('Error al buscar por CI:', error);
        alert('Hubo un error al realizar la búsqueda por CI.');
    }
}

function llenarFormulario(paciente) {



    document.getElementById('cedula').value = paciente.CI || '';
    document.getElementById('nombres').value = paciente.Nombre || '';
    document.getElementById('fecha_nacimiento').value = paciente.FechaNac || '';
    document.getElementById('edad').value = paciente.Edad || '';
    document.getElementById('sexo').value = paciente.Sexo || '';
    document.getElementById('codtelf').value = paciente.CodTelf || '';
    document.getElementById('telefono').value = paciente.Teléfono || '';
    document.getElementById('direccion').value = paciente.Dirección || '';
    // document.getElementById('procedencia').value = paciente.Procedencia || '';
    // document.getElementById('medico').value = paciente.Dr || '';
    document.getElementById('biopsia').value = paciente.NB || '';
    document.getElementById('citologia').value = paciente.NC || '';
    document.getElementById('idPaciente').value = paciente.id || '';
    
}

///////patologos

async function cargarPatologo() {
    try {
        // Hacer la solicitud al endpoint
        const response = await fetch('http://localhost:8000/api/patologos');
        const patologos = await response.json();

        // Obtener el select
        const selectPatologos = document.getElementById('medicoPatologo1');

        // Limpiar el select (opcional)
        selectPatologos.innerHTML = '<option value="">Seleccione</option>';

        // Llenar el select con los datos
        patologos.forEach(patologo => {
            const option = document.createElement('option');
            option.textContent = patologo.Patologo; // Valor que se enviará al backend
            // option.textContent = diagcito.Descripcion; // Texto que se mostrará en el select
            selectPatologos.appendChild(option);
        });
    } catch (error) {
        console.error('Error al cargar los datos:', error);
    }
}

// Llamar a la función cuando la página cargue
document.addEventListener('DOMContentLoaded', cargarPatologo);

////////diagcito
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

////////////////anexo

async function cargaAnexo() {
    try {
        // Hacer la solicitud al endpoint
        const response = await fetch('http://localhost:8000/api/anexos');
        const anexos = await response.json();

        // Guardar los datos en una variable global para usarlos después
        window.anexosData = anexos;

        // Obtener el select
        const selectAnexos = document.getElementById('anexo');

        // Limpiar el select
        selectAnexos.innerHTML = '<option value="">Seleccione</option>';

        // Llenar el select con los datos
        anexos.forEach(anexo => {
            const option = document.createElement('option');
            option.value = anexo.Cod; // Valor que se enviará al backend
            option.textContent = anexo.Cod; // Texto que se mostrará en el select
            selectAnexos.appendChild(option);
        });

        // Agregar event listener para cuando cambie la selección
        selectAnexos.addEventListener('change', function() {
            const codSeleccionado = this.value;
            if (!codSeleccionado) return; // Si es "Seleccione", no hacer nada

            // Buscar el anexo correspondiente al código seleccionado
            const anexoSeleccionado = window.anexosData.find(a => a.Cod === codSeleccionado);

            if (anexoSeleccionado) {
                const textarea = document.getElementById('descripcionCitologia');
                const contenidoActual = textarea.value.trim();
                const nuevoContenido = anexoSeleccionado.Anexo || '';

                // Si ya hay contenido, agregar el nuevo valor en una nueva línea
                if (contenidoActual) {
                    textarea.value = `${contenidoActual}\n${nuevoContenido}`;
                } else {
                    textarea.value = nuevoContenido;
                }
            }
        });
    } catch (error) {
        console.error('Error al cargar los datos:', error);
    }
}

// Llamar a la función cuando la página cargue
document.addEventListener('DOMContentLoaded', cargaAnexo);