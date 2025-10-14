@extends('layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1>Editar Paciente</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('pacientes.show', $paciente->id) }}" class="btn btn-info">Ver</a>
            <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>

    <form action="{{ route('pacientes.update', $paciente->id) }}" method="POST" id="miFormulario">
        @csrf
        @method('PUT')
        
        <!-- Frame: Identificación -->
        <div class="frame mb-4 p-3 border rounded">
            <h3 class="mb-4">Identificación</h3>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fechaActual" class="form-label">Fecha de Ingreso:</label>
                    <input type="text" value="{{ $paciente->created_at }}" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="cedula" class="form-label">Cédula Número:</label>
                    <input type="text" id="cedula" name="cedula" class="form-control" value="{{ $paciente->CI }}" required>
                    @error('cedula')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombres:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="{{ $paciente->Nombre }}" required>
                    @error('nombre')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <div class="col-md-6 mb-3">
                    <label for="sexo" class="form-label">Sexo:</label>
                    <input type="text" id="sexo" name="sexo" class="form-control" value="{{ $paciente->Sexo }}">
                </div>
                    @error('sexo')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="direccion" class="form-label">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" value="{{ $paciente->Direccion }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" class="form-control" value="{{ $paciente->Teléfono }}" pattern="[0-9]{10}" title="10 dígitos">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edad" class="form-label">Edad:</label>
                    <input type="number" id="edad" name="edad" class="form-control" value="{{ $paciente->Edad }}" min="1" max="120" required>
                    @error('edad')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="input-group">
                     <!-- Para Procedencia -->
                        <select id="Procedencia" name="Procedencia" class="form-control" required>
                            <option value="">Seleccione una procedencia...</option>
                            @foreach($procedencias as $procedencia)
                                <option value="{{ $procedencia->Codigo }}" 
                                    {{ $paciente->Procedencia == $procedencia->Codigo ? 'selected' : '' }}>
                                    {{ $procedencia->Codigo }} - {{ $procedencia->Nombre ?? 'Sin nombre' }}
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('procedencias.create') }}" 
                        class="btn btn-outline-secondary"
                        title="Agregar nueva procedencia">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
            <!-- Para Médico -->
                    <div class="input-group">
                        <select id="Dr" name="Dr" class="form-control" required>
                            <option value="">Seleccione un médico...</option>
                            @foreach($medicos as $medico)
                                <option value="{{ $medico->Codigo }}" 
                                    {{ $paciente->Dr == $medico->Codigo ? 'selected' : '' }}>
                                    {{ $medico->Codigo }} - {{ $medico->Nombre ?? 'Sin nombre' }}
                                    
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('medicos.create') }}" 
                        class="btn btn-outline-secondary"
                        title="Agregar nuevo médico">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                <div class="col-md-6 mb-3">
                    <label for="Mail" class="form-label">Correo Electrónico:</label>
                    <input type="email" id="Mail" name="Mail" class="form-control" value="{{ $paciente->mail }}" required>
                    @error('Mail')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

                <!-- Frame: Citologías y Biopsias -->
        <div class="frame mb-4 p-3 border rounded">
            <h3 class="mb-4">Biopsias y Citologías</h3>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="NB" class="form-label">Número de Biopsia:</label>
                    <div class="input-group">
                        <input type="number" id="NB" name="NB" class="form-control" value="{{ $paciente->NB }}" min="1">
                        <button type="submit" name="btnBiopsia" value="1" class="btn btn-outline-primary">
                            Biopsia
                        </button>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="NC" class="form-label">Número de Citología:</label>
                    <div class="input-group">
                        <input type="number" id="NC" name="NC" class="form-control" value="{{ $paciente->NC }}" min="1">
                        <button type="submit" name="btnCitologia" value="1" class="btn btn-outline-success">
                            Citología
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Mostrar status actual -->
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <strong>Estado actual:</strong> 
                        @if($paciente->status == 0)
                            <span class="badge badge-warning">Pendiente</span>
                        @elseif($paciente->status == 1)
                            <span class="badge badge-info">Compleatdo</span>
                        {{-- @else
                            <span class="badge badge-secondary">Pendiente</span> --}}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de guardado normales -->
        {{-- <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Actualizar Paciente</button>
                <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </div> --}}
    </form>
</div>

@endsection

