@extends('layouts.layout')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>LABORATORIO DE ANATOMIA PATOLOGICA "Dr. Jose R. Becker Reyes"</h1>
            <h2>Detalle de Citología</h2>
        </div>
        <div>
            <a href="{{ route('citologias.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            {{-- <a href="{{ route('citologias.edit', $citologia->paciente->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a> --}} 
            <a href="{{ route('citologias.imprimir', $citologia->IdPaciente) }}" class="btn btn-primary" target="_blank">
                <i class="fas fa-print"></i> Imprimir
            </a>
        </div>
    </div>

    <!-- Información del Paciente -->
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
            <div class="row mt-2">
                <div class="col-md-3">
                    <strong>Recibo:</strong> {{ $citologia->paciente->id ?? 'N/A' }}
                </div>
                <div class="col-md-3">
                    <strong>Sexo:</strong> {{ $citologia->paciente->Sexo ?? 'N/A' }}
                </div>
                <div class="col-md-3">
                    <strong>Edad:</strong> {{ $citologia->paciente->Edad ?? 'N/A' }}
                </div>
                <div class="col-md-3">
                    <strong>Teléfono:</strong> {{ $citologia->paciente->Teléfono ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Información de la Citología -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0">Información de la Muestra</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Fecha Recepción:</strong> 
                    @if($citologia->FechaRecibido)
                        {{ \Carbon\Carbon::parse($citologia->FechaRecibido)->format('d/m/Y') }}
                    @else
                        N/A
                    @endif
                </div>
                <div class="col-md-6">
                    <strong>Fecha Envío:</strong> 
                    @if($citologia->FechaEnvio)
                        {{ \Carbon\Carbon::parse($citologia->FechaEnvio)->format('d/m/Y') }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Material Remitido:</strong> {{ $citologia->MaterialRemitido ?? 'N/A' }}
                </div>
                <div class="col-md-6">
                    <strong>Diagnóstico Clínico:</strong> {{ $citologia->DiagnosticoClinico ?? 'N/A' }}
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
            <div class="row">
                <div class="col-md-12 mb-3">
                    <strong>Calidad de la Muestra:</strong>
                    <p class="mt-1">{{ $citologia->Calidad ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <strong>Descripción Macroscópica:</strong>
                    <p class="mt-1">{{ $citologia->DescripcionMacroscopica ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <strong>Descripción Citológica:</strong>
                    <p class="mt-1">{{ $citologia->DescripcionCitologica ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <strong>Diagnóstico Citológico:</strong>
                    <p class="mt-1">{{ $citologia->DiagnosticoCitologico ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <strong>Notas:</strong>
                    <p class="mt-1">{{ $citologia->Notas ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Índice Hormonal -->
    @if($citologia->Superficiales || $citologia->Intermedias || $citologia->Parabasales)
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">Índice de Valoración Hormonal</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Superficiales:</strong> {{ $citologia->Superficiales ?? '0' }}
                </div>
                <div class="col-md-4">
                    <strong>Intermedias:</strong> {{ $citologia->Intermedias ?? '0' }}
                </div>
                <div class="col-md-4">
                    <strong>Parabasales:</strong> {{ $citologia->Parabasales ?? '0' }}
                </div>
            </div>
            @if($citologia->Conclusion)
            <div class="row mt-2">
                <div class="col-md-12">
                    <strong>Conclusión:</strong>
                    <p class="mt-1">{{ $citologia->Conclusion }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Médicos -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h4 class="mb-0">Médicos Patólogos</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Médico Patólogo 1:</strong> {{ $citologia->MedicoPatologo1 ?? 'N/A' }}
                </div>
                <div class="col-md-6">
                    <strong>Médico Patólogo 2:</strong> {{ $citologia->MedicoPatologo2 ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection