@extends('layouts.layout')

@section('content')

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>LABORATORIO DE ANATOMIA PATOLOGICA "Dr. Jose R. Becker Reyes"</h1>
                <h2>Lista de Biopsias</h2>
            </div>
            <a href="{{ route('biopsias.create') }}" class="btn btn-custom">
                <i class="fas fa-plus"></i> Nueva Biopsia
            </a>
        </div>

        <!-- Formulario de Búsqueda -->
        <div class="card search-form">
            <div class="card-body">
                <form action="{{ route('biopsias.index') }}" method="GET" class="form-inline">
                    <div class="form-group mr-3 mb-2">
                        <label for="search" class="sr-only">Buscar por número de biopsia</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Buscar por NB, Cédula...">
                    </div>
                    <button type="submit" class="btn btn-primary mb-2">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('biopsias.index') }}" class="btn btn-secondary mb-2 ml-2">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    @endif
                </form>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($biopsias->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No. Biopsia</th>
                            <th>Paciente</th>
                            {{-- <th>C.I.</th> --}}
                            <th>Fecha Recibido</th>
                            <th>Fecha Envío</th>
                            <th>Material Remitido</th>
                            <th>Sitio Lesión</th>
                            <th>Descripción Macroscópica</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                            @foreach($biopsias as $biopsia)
                            <tr>
                            <td>
                                <strong>{{ $biopsia->paciente->NB ?? 'N/A' }}</strong>
                            </td>
                            <td>
                                    @if($biopsia->paciente)
                                        {{ $biopsia->paciente->Nombre ?? 'N/A' }}
                                    @else
                                        Paciente no encontrado (ID: {{ $biopsia->id }})
                                    @endif
                                </td>
                                {{-- <td>{{ $biopsia->paciente->CI }}</td> --}}
                                <td>{{ $biopsia->FechaRecibido }}</td>
                                <td>{{ $biopsia->FechaEnvio }}</td>
                                <td>{{ $biopsia->MaterialRemitido }}</td>
                                <td>{{ $biopsia->SitioLesion }}</td>
                                <td>{{ Str::limit($biopsia->DescripciónMacroscópica, 50) }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('biopsias.show', $biopsia->id) }}" class="btn btn-info btn-sm">Ver</a>
                                        <a href="{{ route('biopsias.edit', $biopsia->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                        <form action="{{ route('biopsias.destroy', $biopsia->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta biopsia?')">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                No hay biopsias registradas.
            </div>
        @endif
    </div>

      <!-- Paginación -->
        @if($biopsias->hasPages())
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Mostrando {{ $biopsias->firstItem() }} a {{ $biopsias->lastItem() }} 
                    de {{ $biopsias->total() }} registros
                </div>
                <div>
                    {{ $biopsias->links() }}
                </div>
            </div>
        @endif

        <!-- Estadísticas rápidas -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total</h5>
                        <h3 class="card-text">{{ $biopsias->total() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Hoy</h5>
                        <h3 class="card-text">
                            {{ \App\Models\Biopsia::whereDate('created_at', today())->count() }}
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Este Mes</h5>
                        <h3 class="card-text">
                            {{ \App\Models\Biopsia::whereMonth('created_at', now()->month)->count() }}
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Pendientes</h5>
                        <h3 class="card-text">
                            {{ \App\Models\Biopsia::whereNull('FechaEnvio')->count() }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    @section('scripts')
    <script>
        // Auto-focus en el campo de búsqueda
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.focus();
            }
        });
    </script>
    @endsection
