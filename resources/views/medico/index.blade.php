<!-- resources/views/medicos/index.blade.php -->
@extends('layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-md me-2"></i>Gestión de Médicos
        </h1>
        <a href="{{ route('medicos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Médico
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
                <i class="fas fa-list me-2"></i>Lista de Médicos
            </h6>
        </div>
        <div class="card-body">
            <!-- Barra de búsqueda y filtros -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <form action="{{ route('medicos.index') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Buscar por nombre, especialidad o email..." 
                                   value="{{ request('search') }}">
                            <select name="activo" class="form-control ml-2" style="max-width: 150px;">
                                <option value="">Todos los estados</option>
                                <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Activos</option>
                                <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                @if(request()->hasAny(['search', 'activo']))
                                    <a href="{{ route('medicos.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-right">
                    <span class="text-muted">
                        Total: {{ $medicos->total() }} médico(s)
                    </span>
                </div>
            </div>

            <!-- Tabla de médicos -->
            @if($medicos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th width="50">Codigo</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                             </tr>
                        </thead>
                        <tbody>
                            @foreach($medicos as $medico)
                            <tr>
                                <td>{{ $medico->Codigo }}</td>
                                <td>
                                    <strong>{{ $medico->Codigo }}</strong>
                                </td>
                                {{-- <td>
                                    @if($medico->especialidad)
                                        <span class="badge badge-info">{{ $medico->especialidad }}</span>
                                    @else
                                        <span class="text-muted">Sin especificar</span>
                                    @endif
                                </td> --}}
                                <td>
                                    @if($medico->Telefono)
                                        <i class="fas fa-phone me-1 text-muted"></i>{{ $medico->Telefono }}
                                    @else
                                        <span class="text-muted">No asignado</span>
                                    @endif
                                </td>
                                {{-- <td>
                                    @if($medico->email)
                                        <i class="fas fa-envelope me-1 text-muted"></i>{{ $medico->email }}
                                    @else
                                        <span class="text-muted">No asignado</span>
                                    @endif
                                </td> 
                                <td>
                                    @if($medico->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td> --}}
                              {{--  <td class="text-center">
                                    <span class="badge badge-primary">{{ $medico->pacientes_count ?? $medico->pacientes()->count() }}</span>
                                </td> --}}
                                <td>
                                    <div class="btn-group" role="group">
                                        {{-- <a href="{{ route('medicos.edit', $medico->id) }}" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a> --}}
                                        
                                        {{-- <form action="{{ route('medicos.toggle-status', $medico->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-sm {{ $medico->activo ? 'btn-secondary' : 'btn-success' }}"
                                                    title="{{ $medico->activo ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas {{ $medico->activo ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                            </button>
                                        </form> --}}
                                        
                                        <form action="{{ route('medicos.destroy', $medico->Codigo) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('¿Estás seguro de eliminar este médico?')"
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
                        Mostrando {{ $medicos->firstItem() }} a {{ $medicos->lastItem() }} de {{ $medicos->total() }} registros
                    </div>
                    <div>
                        {{ $medicos->appends(request()->query())->links() }}
                    </div>
                </div>
                
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-md fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No se encontraron médicos</h4>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'activo']))
                            Intenta ajustar los filtros de búsqueda
                        @else
                            Comienza agregando el primer médico
                        @endif
                    </p>
                    <a href="{{ route('medicos.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>Agregar Primer Médico
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