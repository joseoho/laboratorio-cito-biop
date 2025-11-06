@extends('layout.layout')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>LABORATORIO DE ANATOMIA PATOLOGICA "Dr. Jose R. Becker Reyes"</h1>
            <h2>Editar Citología</h2>
        </div>
        <div>
            <a href="{{ route('biopsias.show', $biopsia->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Ver
            </a>
            <a href="{{ route('biopsia.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <form action="{{ route('biopsia.update', $biopsia->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Información del Paciente (solo lectura) -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Información del Paciente</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>No. Citología:</strong> {{ $citologia->paciente->NC ?? 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Cédula:</strong> {{ $citologia->paciente->CI ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Nombre:</strong> {{ $citologia->paciente->Nombre ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de la Muestra -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">Información de la Muestra</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="FechaRecibido">Fecha Recepción:</label>
                            <input type="date" class="form-control" id="FechaRecibido" name="FechaRecibido" 
                                   value="{{ $citologia->FechaRecibido ? \Carbon\Carbon::parse($citologia->FechaRecibido)->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="FechaEnvio">Fecha Envío:</label>
                            <input type="date" class="form-control" id="FechaEnvio" name="FechaEnvio" 
                                   value="{{ $citologia->FechaEnvio ? \Carbon\Carbon::parse($citologia->FechaEnvio)->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="MaterialRemitido">Material Remitido:</label>
                            <input type="text" class="form-control" id="MaterialRemitido" name="MaterialRemitido" 
                                   value="{{ $citologia->MaterialRemitido ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="DiagnosticoClinico">Diagnóstico Clínico:</label>
                            <input type="text" class="form-control" id="DiagnosticoClinico" name="DiagnosticoClinico" 
                                   value="{{ $citologia->DiagnosticoClinico ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descripciones -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Descripciones y Diagnósticos</h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="Calidad">Calidad de la Muestra:</label>
                    <textarea class="form-control" id="Calidad" name="Calidad" rows="3">{{ $citologia->Calidad ?? '' }}</textarea>
                </div>
                <div class="form-group">
                    <label for="DescripcionMacroscopica">Descripción Macroscópica:</label>
                    <textarea class="form-control" id="DescripcionMacroscopica" name="DescripcionMacroscopica" rows="3">{{ $citologia->DescripcionMacroscopica ?? '' }}</textarea>
                </div>
                <div class="form-group">
                    <label for="DescripcionCitologica">Descripción Citológica:</label>
                    <textarea class="form-control" id="DescripcionCitologica" name="DescripcionCitologica" rows="3">{{ $citologia->DescripcionCitologica ?? '' }}</textarea>
                </div>
                <div class="form-group">
                    <label for="DiagnosticoCitologico">Diagnóstico Citológico:</label>
                    <textarea class="form-control" id="DiagnosticoCitologico" name="DiagnosticoCitologico" rows="3">{{ $citologia->DiagnosticoCitologico ?? '' }}</textarea>
                </div>
                <div class="form-group">
                    <label for="Notas">Notas:</label>
                    <textarea class="form-control" id="Notas" name="Notas" rows="3">{{ $citologia->Notas ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <!-- Índice Hormonal -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">Índice de Valoración Hormonal</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Superficiales">Superficiales:</label>
                            <input type="number" class="form-control" id="Superficiales" name="Superficiales" 
                                   value="{{ $citologia->Superficiales ?? 0 }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Intermedias">Intermedias:</label>
                            <input type="number" class="form-control" id="Intermedias" name="Intermedias" 
                                   value="{{ $citologia->Intermedias ?? 0 }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Parabasales">Parabasales:</label>
                            <input type="number" class="form-control" id="Parabasales" name="Parabasales" 
                                   value="{{ $citologia->Parabasales ?? 0 }}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="Conclusion">Conclusión:</label>
                    <textarea class="form-control" id="Conclusion" name="Conclusion" rows="3">{{ $citologia->Conclusion ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <!-- Médicos -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0">Médicos Patólogos</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="MedicoPatologo1">Médico Patólogo 1:</label>
                            <input type="text" class="form-control" id="MedicoPatologo1" name="MedicoPatologo1" 
                                   value="{{ $citologia->MedicoPatologo1 ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="MedicoPatologo2">Médico Patólogo 2:</label>
                            <input type="text" class="form-control" id="MedicoPatologo2" name="MedicoPatologo2" 
                                   value="{{ $citologia->MedicoPatologo2 ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="text-center">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save"></i> Actualizar Citología
            </button>
            <a href="{{ route('citologias.show', $citologia->id) }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection