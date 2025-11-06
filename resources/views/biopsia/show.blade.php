@extends('layouts.layout')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Detalle de Biopsia {{ $biopsia->paciente->NB ?? 'N/A' }}</h1>
            <div>
            <a href="{{ route('biopsias.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('biopsias.edit', $biopsia->paciente->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a> 
            <a href="{{ route('biopsias.imprimir', $biopsia->id) }}" class="btn btn-primary" target="_blank">
                <i class="fas fa-print"></i> Imprimir
            </a>
        </div>
        </div>

        <div class="row">
            <!-- Columna izquierda - Información básica -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Información General</h5>
                    </div>
                    <div class="card-body">
                            <div class="detail-card">
                                <strong>Paciente:</strong>
                                <p class="mb-0">{{ $biopsia->paciente->Nombre ?? 'N/A' }}</p>
                            </div>
                        <div class="detail-card">
                            <strong>Fecha de Recibido:</strong>
                            <p class="mb-0">{{ $biopsia->FechaRecibido ?? 'No especificado' }}</p>
                        </div>
                        
                        <div class="detail-card">
                            <strong>Fecha de Envío:</strong>
                            <p class="mb-0">{{ $biopsia->FechaEnvio ?? 'No especificado' }}</p>
                        </div>
                        
                        <div class="detail-card">
                            <strong>Material Remitido:</strong>
                            <p class="mb-0">{{ $biopsia->MaterialRemitido ?? 'No especificado' }}</p>
                        </div>
                        
                        <div class="detail-card">
                            <strong>Diagnóstico Clínico:</strong>
                            <p class="mb-0">{{ $biopsia->DiagnosticoClinico ?? 'No especificado' }}</p>
                        </div>
                        
                        <div class="detail-card">
                            <strong>Sitio de la Lesión:</strong>
                            <p class="mb-0">{{ $biopsia->SitioLesion ?? 'No especificado' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sección de Médicos Patólogos -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Médicos Patólogos</h5>
                    </div>
                    <div class="card-body">
                        <div class="detail-card">
                            <strong>Médico Patólogo 1:</strong>
                            <p class="mb-0">{{ $biopsia->MedicoPatólogo1 ?? 'No asignado' }}</p>
                        </div>
                        
                        <div class="detail-card">
                            <strong>Médico Patólogo 2:</strong>
                            <p class="mb-0">{{ $biopsia->MedicoPatólogo2 ?? 'No asignado' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha - Descripciones y diagnóstico -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Descripciones y Diagnóstico</h5>
                    </div>
                    <div class="card-body">
                        <div class="detail-card">
                            <strong>Descripción Macroscópica:</strong>
                            <p class="mb-0">{{ $biopsia->DescripciónMacroscópica ?? 'No disponible' }}</p>
                        </div>
                        
                        <div class="detail-card">
                            <strong>Descripción Microscópica:</strong>
                            <p class="mb-0">{{ $biopsia->DescripciónMicroscópica ?? 'No disponible' }}</p>
                        </div>
                        
                        <div class="detail-card">
                            <strong>Diagnóstico Microscópico:</strong>
                            <p class="mb-0 text-danger fw-bold">{{ $biopsia->DiagnósticoMicroscópico ?? 'No disponible' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sección de Notas y Comentarios -->
                <div class="card mt-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Notas y Comentarios</h5>
                    </div>
                    <div class="card-body">
                        <div class="detail-card">
                            <strong>Notas:</strong>
                            <p class="mb-0">{{ $biopsia->Notas ?? 'Sin notas' }}</p>
                        </div>
                        
                        <div class="detail-card">
                            <strong>Comentario 1:</strong>
                            <p class="mb-0">{{ $biopsia->Com1 ?? 'Sin comentario' }}</p>
                        </div>
                        
                        <div class="detail-card">
                            <strong>Comentario 2:</strong>
                            <p class="mb-0">{{ $biopsia->Com2 ?? 'Sin comentario' }}</p>
                        </div>
                        
                        <div class="detail-card">
                            <strong>Comentario 3:</strong>
                            <p class="mb-0">{{ $biopsia->Com3 ?? 'Sin comentario' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sección de Fotos -->
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Fotos Adjuntas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($biopsia->Foto_1)
                                <div class="col-md-4 mb-2">
                                    <div class="text-center">
                                        <div class="bg-light p-2 rounded">
                                            <small>Foto 1</small>
                                            <div class="mt-1">
                                                <span class="badge bg-success">Adjunta</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($biopsia->Foto_2)
                                <div class="col-md-4 mb-2">
                                    <div class="text-center">
                                        <div class="bg-light p-2 rounded">
                                            <small>Foto 2</small>
                                            <div class="mt-1">
                                                <span class="badge bg-success">Adjunta</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($biopsia->Foto_3)
                                <div class="col-md-4 mb-2">
                                    <div class="text-center">
                                        <div class="bg-light p-2 rounded">
                                            <small>Foto 3</small>
                                            <div class="mt-1">
                                                <span class="badge bg-success">Adjunta</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if(!$biopsia->Foto_1 && !$biopsia->Foto_2 && !$biopsia->Foto_3)
                                <div class="col-12">
                                    <p class="text-muted text-center mb-0">No hay fotos adjuntas</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción en la parte inferior -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('biopsias.index') }}" class="btn btn-secondary">← Volver a la lista</a>
                    <div>
                        <a href="{{ route('biopsias.edit', $biopsia->id) }}" class="btn btn-warning">Editar</a>
                        <form action="{{ route('biopsias.destroy', $biopsia->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta biopsia?')">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection