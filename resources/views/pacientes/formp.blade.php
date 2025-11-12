@extends('layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1>Nuevo Paciente</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">Volver al Listado</a>
        </div>
    </div>

    <!-- Mostrar mensajes de error -->
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pacientes.store') }}" method="POST" id="formPaciente">
        @csrf
        
        <!-- Frame: Identificación -->
        <div class="frame mb-4 p-3 border rounded">
            <h3 class="mb-4">Identificación</h3>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fechaActual" class="form-label">Fecha de Ingreso:</label>
                    <input type="text" id="fechaActual" name="fechaActual" class="form-control" value="{{ date('Y-m-d') }}" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="CI" class="form-label">Cédula Número: *</label>
                    <input type="text" id="CI" name="CI" class="form-control" value="{{ old('CI') }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="Nombre" class="form-label">Nombres: *</label>
                    <input type="text" id="Nombre" name="Nombre" class="form-control" value="{{ old('Nombre') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="Sexo" class="form-label">Sexo: *</label>
                    <select id="Sexo" name="Sexo" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <option value="M" {{ old('Sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('Sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ireccion" class="form-label">Dirección:</label>
                    <input type="text" id="Dirección" name="Dirección" class="form-control" value="{{ old('Dirección') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input type="text" id="Teléfono" name="Teléfono" class="form-control" value="{{ old('Teléfono') }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="Edad" class="form-label">Edad:</label>
                    <input type="number" id="Edad" name="Edad" class="form-control" value="{{ old('Edad') }}" min="1" max="120">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="Procedencia" class="form-label">Procedencia: *</label>
                    <div class="input-group">
                        <select id="Procedencia" name="Procedencia" class="form-control" required>
                            <option value="">Seleccione una procedencia...</option>
                            @foreach($procedencias as $procedencia)
                                <option value="{{ $procedencia->Codigo }}" {{ old('Procedencia') == $procedencia->Codigo ? 'selected' : '' }}>
                                    {{ $procedencia->Codigo }} - {{ $procedencia->Nombre ?? 'Sin nombre' }}
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('procedencias.create') }}" class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="Dr" class="form-label">Médico: *</label>
                    <div class="input-group">
                        <select id="Dr" name="Dr" class="form-control" required>
                            <option value="">Seleccione un médico...</option>
                            @foreach($medicos as $medico)
                                <option value="{{ $medico->Codigo }}" {{ old('Dr') == $medico->Codigo ? 'selected' : '' }}>
                                    {{ $medico->Codigo }} - {{ $medico->Nombre ?? 'Sin nombre' }}
                                    @if($medico->especialidad)
                                        ({{ $medico->especialidad }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('medicos.create') }}" class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="mail" class="form-label">Correo Electrónico: *</label>
                    <input type="email" id="mail" name="mail" class="form-control" value="{{ old('mail') }}" required>
                </div>
            </div>
        </div>

        <!-- Frame: Biopsias y Citologías -->
        <div class="frame mb-4 p-3 border rounded">
            <h3 class="mb-4">Biopsias y Citologías</h3>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="NB" class="form-label">Número de Biopsia:</label>
                    <input type="number" id="NB" name="NB" class="form-control" value="{{ old('NB') }}" min="1">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="NC" class="form-label">Número de Citología:</label>
                    <input type="number" id="NC" name="NC" class="form-control" value="{{ old('NC') }}" min="1">
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="row">
            <div class="col-md-12">
                <button type="submit" id="btnGuardar" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Paciente
                </button>
                <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formPaciente');
    const btnGuardar = document.getElementById('btnGuardar');
    
    console.log('Formulario cargado correctamente');
    console.log('Form action:', form.action);
    console.log('Form method:', form.method);
    
    // Verificar que todos los campos tengan name
    const campos = form.querySelectorAll('input, select, textarea');
    campos.forEach(campo => {
        if (!campo.name && campo.id && campo.type !== 'submit') {
            console.warn('Campo sin name attribute:', campo.id);
        }
    });
    
    form.addEventListener('submit', function(e) {
        console.log('=== FORMULARIO ENVIANDO ===');
        
        // Validar campos requeridos
        const camposRequeridos = form.querySelectorAll('[required]');
        let formularioValido = true;
        
        camposRequeridos.forEach(campo => {
            if (!campo.value.trim()) {
                console.error('Campo requerido vacío:', campo.name);
                campo.classList.add('is-invalid');
                formularioValido = false;
            } else {
                campo.classList.remove('is-invalid');
            }
        });
        
        if (!formularioValido) {
            e.preventDefault();
            alert('Por favor complete todos los campos requeridos');
            return;
        }
        
        console.log('Formulario válido, enviando...');
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    });
});
</script>

<style>
.is-invalid {
    border-color: #dc3545 !important;
}
</style>
@endsection