<!-- resources/views/anexos/index.blade.php -->
@extends('layouts.layout')

@section('content')
    <div class="container mt-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-file-earmark-text"></i> Gestión de Plantillas
            </h1>
            <a href="{{ route('plantillas.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva Plantilla
            </a>
        </div>

        <!-- Mensajes de éxito -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Buscador -->
        <div class="search-box">
            <form action="{{ route('plantillas.index') }}" method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Buscar plantillas por nombre..." 
                               value="{{ $search }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    @if($search)
                        <a href="{{ route('plantillas.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Limpiar búsqueda
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">{{ $plantillas->total() }}</h5>
                        <p class="card-text">Total Plantillas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">{{ $plantillas->where('modulo', '00')->count() }}</h5>
                        <p class="card-text">Módulo 00 (Macro)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">{{ $plantillas->where('modulo', '01')->count() + $plantillas->where('modulo', '02')->count() }}</h5>
                        <p class="card-text">Módulos 01-02 (Micro)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de plantillas -->
        @if($plantillas->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="80">ID</th>
                            <th width="100">Módulo</th>
                            <th>Nombre</th>
                            <th>Contenido</th>
                            <th width="200">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plantillas as $plantilla)
                            <tr>
                                <td><strong>#{{ $plantilla->id }}</strong></td>
                                <td>
                                    @if($plantilla->modulo == '00')
                                        <span class="badge bg-success badge-modulo">00 - Macro</span>
                                    @elseif($plantilla->modulo == '01')
                                        <span class="badge bg-info badge-modulo">01 - Micro</span>
                                    @elseif($plantilla->modulo == '02')
                                        <span class="badge bg-warning text-dark badge-modulo">02 - Diag</span>
                                    @else
                                        <span class="badge bg-secondary badge-modulo">{{ $plantilla->modulo }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $plantilla->nombre }}</strong>
                                </td>
                                <td>
                                    <span class="text-muted" title="{{ $plantilla->contenido }}">
                                        {{ Str::limit($plantilla->contenido, 80) }}
                                    </span>
                                </td>
                                <td>
                                    {{-- <div class="btn-group" role="group">
                                        <a href="{{ route('plantillas.show', $plantilla->id) }}" 
                                           class="btn btn-info btn-sm" 
                                           title="Ver detalles">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('plantillas.edit', $plantilla->id) }}" 
                                           class="btn btn-warning btn-sm" 
                                           title="Editar plantilla">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                        <form action="{{ route('plantillas.destroy', $plantilla->id) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('¿Estás seguro de eliminar esta plantilla?')"
                                                    title="Eliminar plantilla">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </div> --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Mostrando {{ $plantillas->firstItem() }} - {{ $plantillas->lastItem() }} de {{ $plantillas->total() }} registros
                </div>
                {{ $plantillas->appends(['search' => $search])->links() }}
            </div>
        @else
            <!-- Mensaje cuando no hay resultados -->
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i>
                @if($search)
                    No se encontraron plantillas con el nombre "{{ $search }}"
                @else
                    No hay plantillas registradas. 
                    <a href="{{ route('plantillas.create') }}" class="alert-link">Crear la primera plantilla</a>
                @endif
            </div>
        @endif
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection