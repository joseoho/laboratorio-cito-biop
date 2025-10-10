<!-- resources/views/patologos/index.blade.php -->
@extends('layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clinic-medical me-2"></i>Gestión de Procedencias
        </h1>
        <a href="{{ route('procedencias.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Procedencia
        </a>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Lista de Procedencias
            </h6>
        </div>
        <div class="card-body">
            <!-- Barra de búsqueda y filtros -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <form action="{{ route('procedencias.index') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Buscar por código..." 
                                   value="{{ request('search') }}">
                            <select name="activo" class="form-control ml-2" style="max-width: 150px;">
                                <option value="">Todos los estados</option>
                                <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Activas</option>
                                <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Inactivas</option>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                @if(request()->hasAny(['search', 'activo']))
                                    <a href="{{ route('procedencias.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-right">
                    <span class="text-muted">
                        Total: {{ $procedencias->total() }} procedencia(s)
                    </span>
                </div>
            </div>

            <!-- Tabla de procedencias -->
            @if($procedencias->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                {{-- <th width="50">ID</th> --}}
                                <th>Código</th>
                                <th>Lista de Precio</th>
                                {{-- <th>Estado</th> --}}
                                <th width="150">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($procedencias as $procedencia)
                            <tr>
                               {{-- <td>{{ $procedencia->id }}</td> --}}
                                <td>
                                    <strong>{{ $procedencia->Codigo }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-success">${{ $procedencia->ListaPrecio, 2 }}</span>
                                </td>
                                {{-- <td>
                                    @if($procedencia->activo)
                                        <span class="badge badge-success">Activa</span>
                                    @else
                                        <span class="badge badge-danger">Inactiva</span>
                                    @endif
                                </td> --}}
                                <td>
                                    {{-- <div class="btn-group" role="group">
                                        <a href="{{ route('procedencias.edit', $procedencia->Codigo) }}" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form action="{{ route('procedencias.toggle-status', $procedencia->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-sm {{ $procedencia->activo ? 'btn-secondary' : 'btn-success' }}"
                                                    title="{{ $procedencia->activo ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas {{ $procedencia->activo ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                            </button>
                                        </form> --}}
                                        
                                        <form action="{{ route('procedencias.destroy', $procedencia->Codigo) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('¿Estás seguro de eliminar esta procedencia?')"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div> 
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Mostrando {{ $procedencias->firstItem() }} a {{ $procedencias->lastItem() }} de {{ $procedencias->total() }} registros
                    </div>
                    <div>
                        {{ $procedencias->appends(request()->query())->links() }}
                    </div>
                </div>
                
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clinic-medical fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No se encontraron procedencias</h4>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'activo']))
                            Intenta ajustar los filtros de búsqueda
                        @else
                            Comienza agregando la primera procedencia
                        @endif
                    </p>
                    <a href="{{ route('procedencias.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>Agregar Primera Procedencia
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-ocultar alertas después de 5 segundos
    $(document).ready(function() {
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    });
</script>
@endsection