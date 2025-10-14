@extends('layouts.layout')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Citologías - LABORATORIO BECOMARCA</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .btn-custom {
            background-color: #28a745;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
        }
        .btn-custom:hover {
            background-color: #218838;
        }
        .search-form {
            margin-bottom: 20px;
        }
        .badge-new {
            background-color: #17a2b8;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body>
    @extends('layout.layout')
    
    @section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>LABORATORIO DE ANATOMIA PATOLOGICA "Dr. Jose R. Becker Reyes"</h1>
                <h2>Lista de Citologías</h2>
            </div>
            <a href="{{ route('citologias.create') }}" class="btn btn-custom">
                <i class="fas fa-plus"></i> Nueva Citología
            </a>
        </div>

        <!-- Formulario de Búsqueda -->
        <div class="card search-form">
            <div class="card-body">
                <form action="{{ route('citologias.index') }}" method="GET" class="form-inline">
                    <div class="form-group mr-3 mb-2">
                        <label for="search" class="sr-only">Buscar por número de citología</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Buscar por número de citología...">
                    </div>
                    <button type="submit" class="btn btn-primary mb-2">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('citologias.index') }}" class="btn btn-secondary mb-2 ml-2">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Información de resultados -->
        @if(request('search'))
            <div class="alert alert-info">
                @if($citologias->total() > 0)
                    Se encontraron {{ $citologias->total() }} resultado(s) para "{{ request('search') }}"
                @else
                    No se encontraron resultados para "{{ request('search') }}"
                @endif
            </div>
        @endif

        <!-- Tabla de Citologías -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>No. Citología</th>
                        <th>Paciente</th>
                        <th>C.I.</th>
                        <th>Recibo</th>
                        <th>Fecha Recepción</th>
                        <th>Fecha Envío</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
    @forelse($citologias as $citologia)
        <tr>
            <td>
                <strong>{{ $citologia->paciente->NC ?? 'N/A' }}</strong>
            </td>
            <td>
                @if($citologia->paciente)
                    {{ $citologia->paciente->Nombre ?? 'N/A' }}
                @else
                    Paciente no encontrado (ID: {{ $citologia->IdPaciente }})
                @endif
            </td>
            <td>{{ $citologia->paciente->CI ?? 'N/A' }}</td>
            <td>{{ $citologia->paciente->id ?? 'N/A' }}</td>
            <td>
                @if($citologia->FechaRecibido)
                    {{ \Carbon\Carbon::parse($citologia->FechaRecibido) }}
                @else
                    N/A
                @endif
            </td>
            <td>
                @if($citologia->FechaEnvio)
                    {{ \Carbon\Carbon::parse($citologia->FechaEnvio) }}
                @else
                    N/A
                @endif
            </td>
            <td>
                @if($citologia->created_at)
                    <span class="badge badge-new">Nuevo</span>
                @else
                    <span class="badge badge-pending">Procesado</span>
                @endif
            </td>
            <td>
                {{-- <div class="btn-group" role="group">
                    <a href="{{ route('citologias.show', $citologia->id) }}" 
                       class="btn btn-info btn-sm" 
                       title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('citologias.edit', $citologia->id) }}" 
                       class="btn btn-warning btn-sm" 
                       title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="{{ route('citologias.imprimir', $citologia->id) }}" 
                       class="btn btn-secondary btn-sm" 
                       title="Imprimir" target="_blank">
                        <i class="fas fa-print"></i>
                    </a>
                    <form action="{{ route('citologias.destroy', $citologia->id) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('¿Está seguro de eliminar esta citología?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form> --}}
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="text-center">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    No se encontraron citologías registradas.
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($citologias->hasPages())
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Mostrando {{ $citologias->firstItem() }} a {{ $citologias->lastItem() }} 
                    de {{ $citologias->total() }} registros
                </div>
                <div>
                    {{ $citologias->links() }}
                </div>
            </div>
        @endif

        <!-- Estadísticas rápidas -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total</h5>
                        <h3 class="card-text">{{ $citologias->total() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Hoy</h5>
                        <h3 class="card-text">
                            {{ \App\Models\Citologia::whereDate('created_at', today())->count() }}
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Este Mes</h5>
                        <h3 class="card-text">
                            {{ \App\Models\Citologia::whereMonth('created_at', now()->month)->count() }}
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Pendientes</h5>
                        <h3 class="card-text">
                            {{ \App\Models\Citologia::whereNull('FechaEnvio')->count() }}
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
</body>
</html>
@endsection