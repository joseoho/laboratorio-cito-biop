<!-- resources/views/biopsia/form.blade.php -->
@extends('layouts.layout')

@section('content')
    <div class="container">
    <div class="row mb-3">
        <div class="col">
            <h1>LABORATORIO DE ANATOMIA PATOLOGICA "Dr. Jose R. Becker Reyes"</h1>
            <h2>Biopsia, Registrar</h2>
        </div>
        <div class="col text-right">
            <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">Volver a Pacientes</a>
        </div>
    </div>

    <!-- Formulario de Biopsia -->
    <form action="{{ route('biopsias.store') }}" method="POST" id="formCitologia">
        @csrf
        
        <!-- Campo oculto para el ID del paciente -->
        <input type="hidden" name="id_paciente" value="{{ $paciente->id}}">

        <!-- Frame de Citología -->
        <div class="frame mb-4 p-3 border rounded">
            <h3>Datos del Paciente</h3>
            <div class="row">
                <div class="col-md-3">
                    <label for="recibo" class="form-label">Recibo:</label>
                    <input type="text" value="{{ $paciente->id }}" name="id" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="ci" class="form-label">C.I No.:</label>
                    <input type="text" value="{{ $paciente->CI }}" name="ci" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="noBiopsia" class="form-label">No. Biopsia:</label>
                    <input type="text" value="{{ $paciente->NB }}" name="nb" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="nombres" class="form-label">Nombres:</label>
                    <input type="text" value="{{ $paciente->Nombre }}" name="nombre" class="form-control" readonly>
                </div>
            </div>
        </div>

        <form action="{{ route('biopsias.store') }}" method="POST">
            @csrf

            <!-- Frame de Identificación de la muestra -->
            <div class="frame mb-4 p-3 border rounded">
                <h3>Identificación de la muestra</h3>
                <div class="row">
                    <div class="col-md-6">
                        <label for="FechaRecibido">Fecha Recib.:</label>
                        <input type="text" name="FechaRecibido" id="FechaRecibido"  value="{{ $paciente->FechaApertura }}" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="fechaEnvio">Fecha Envio:</label>
                        <input type="date" name="FechaEnvio" id="FechaEnvio" class="form-control">
                    </div>
                </div>
            </div>

            <!-- Frame de Material Remitido y Diagnóstico clínico -->
            <div class="frame mb-4 p-3 border rounded">
                <div class="row">
                    <div class="col-md-6">
                        <label for="materialRemitido">Material Remitido:</label>
                        <input type="text" name="MaterialRemitido" id="MaterialRemitido" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="diagnosticoClinico">Diagnóstico clínico:</label>
                        <input type="text" name="DiagnosticoClinico" id="DiagnosticoClinico" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="sitioLesion">Sitio de la Lesión:</label>
                        <input type="text" name="SitioLesion" id="SitioLesion" class="form-control">
                    </div>
                </div>
            </div>

            <div class="frame mb-4 p-3 border rounded">
                <div class="row">
                    <div class="col-md-6">
                        <label for="descripcionMacroscopica">Descripción Macroscópica:</label>
                        <textarea name="DescripciónMacroscópica" id="DescripciónMacroscópica" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="col-md-6">
                        <!-- Descripción Macroscópica -->
                        <div class="col mb-6">
                            <label for="descripcion_macroscopica" class="form-label">Plantilla Descripción Macroscópica</label>
                            <select class="form-select" id="descripcion_macroscopica" name="Descripción_Macroscópica">
                                <option value="">Seleccione una descripción macroscópica</option>
                                @foreach($descripcionesMacro as $plantilla)
                                    <option value="{{ $plantilla->contenido }}" 
                                        {{ old('Descripción_Macroscópica', isset($biopsia) ? $biopsia->Descripción_Macroscópica : '') == $plantilla->contenido ? 'selected' : '' }}>
                                        {{ $plantilla->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Frame de Descripción Microscópica -->
            <div class="frame mb-4 p-3 border rounded">
                <div class="row">
                    <div class="col-md-6">
                        <label for="descripcionMicroscopica">Descripción Microscópica:</label>
                        <textarea name="DescripciónMicroscópica" id="DescripciónMicroscópica" class="form-control" rows="5"></textarea>
                    </div>
                    <!-- Descripción Microscópica -->
                    <div class="col-md-6">
                        <label for="descripcion_microscopica" class="form-label">Plantilla Descripción Microscópica</label>
                        <select class="form-select" id="descripcion_microscopica" name="Descripción_Microscópica">
                            <option value="">Seleccione una descripción microscópica</option>
                            @foreach($descripcionesMicro as $plantilla)
                                <option value="{{ $plantilla->contenido }}" 
                                    {{ old('Descripción_Microscópica', isset($biopsia) ? $biopsia->Descripción_Microscópica : '') == $plantilla->contenido ? 'selected' : '' }}>
                                    {{ $plantilla->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Frame de Diagnóstico Microscópico -->
            <div class="frame mb-4 p-3 border rounded">
                <div class="row">
                    <div class="col-md-6">
                        <label for="diagnosticoMicroscopico">Diagnóstico Microscópico:</label>
                        <textarea name="DiagnósticoMicroscópico" id="DiagnósticoMicroscópico" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="diagnostico_microscopico" class="form-label">Diagnóstico Microscópico</label>
                        <select class="form-select" id="diagnostico_microscopico" name="Diagnóstico_Microscópico">
                            <option value="">Seleccione un diagnóstico microscópico</option>
                            @foreach($diagnosticosMacro as $plantilla)
                                <option value="{{ $plantilla->contenido }}" 
                                    {{ old('Diagnóstico_Microscópico', isset($biopsia) ? $biopsia->Diagnóstico_Microscópico : '') == $plantilla->contenido ? 'selected' : '' }}>
                                    {{ $plantilla->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                    <div class="col-md-12">
                        <label for="notas">Notas:</label>
                        <textarea name="Notas" id="Notas" class="form-control" rows="5"></textarea>
                    </div>
            </div>
<div class="frame mb-4 p-3 border rounded">
            <div class="row">
                <div class="col-md-6">
                    <label for="MedicoPatologo1" class="form-label">Médico Patólogo 1:</label>
                    <select id="MedicoPatólogo1" name="MedicoPatólogo1" class="form-control">
                        <option value="">Seleccione médico...</option>
                        <option value="Dr. Juan Carlos Becker">Dr. Juan Carlos Becker</option>
                        <option value="Dr. Claudio Arrechedera">Dr. Claudio Arrechedera</option>
                        <option value="Cit. Ilia C. Molina">Cit. Ilia C. Molina</option>
                        <option value="Cit. Johaiza Contreras">Cit. Johaiza Contreras</option>
                        <option value="Cit. Kandy Contreras">Cit. Kandy Contreras</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="MedicoPatologo2" class="form-label">Médico Patólogo 2:</label>
                    <select id="MedicoPatólogo2" name="MedicoPatólogo2" class="form-control">
                        <option value="">Seleccione médico...</option>
                        <option value="Dr. Juan Carlos Becker">Dr. Juan Carlos Becker</option>
                        <option value="Dr. Claudio Arrechedera">Dr. Claudio Arrechedera</option>
                        <option value="Cit. Ilia C. Molina">Cit. Ilia C. Molina</option>
                        <option value="Cit. Johaiza Contreras">Cit. Johaiza Contreras</option>
                        <option value="Cit. Kandy Contreras">Cit. Kandy Contreras</option>
                    </select>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Biopsia Completa
                </button>
                <a href="{{ route('pacientes.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

@endsection
<script src="{{ asset('assets/js/llenaplantillas.js') }}"></script>