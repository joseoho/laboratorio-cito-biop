@extends('layouts.layout')

@section('content')
<div class="container">
    <h1 class="text-center mb-4">Lista de Pacientes</h1>

    <!-- Mensajes -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Barra de búsqueda mejorada -->
    <div class="frame mb-4 p-3 border rounded">
        <h3 class="mb-3">Buscar Pacientes</h3>
        <form action="{{ route('pacientes.index') }}" method="GET">
            <div class="row">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Ingrese su búsqueda..." 
                               value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit" name="tipo" value="ci">
                                CI/NOM
                            </button>
                            <button class="btn btn-outline-secondary" type="submit" name="tipo" value="nb">
                                NB
                            </button>
                            <button class="btn btn-outline-secondary" type="submit" name="tipo" value="nc">
                                NC
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('pacientes.create') }}" class="btn btn-primary mr-2">
                            <i class="fas fa-plus"></i> Nuevo Paciente
                        </a>
                        @if(request()->has('search'))
                            <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Mostrar tipo de búsqueda actual -->
            @if(request()->has('search'))
                <div class="mt-2">
                    <small class="text-muted">
                        Buscando por 
                        @if(request('tipo') == 'ci')
                            <strong>Cédula/Nombre</strong>
                        @elseif(request('tipo') == 'nb')
                            <strong>Número de Biopsia</strong>
                        @elseif(request('tipo') == 'nc')
                            <strong>Número de Citología</strong>
                        @else
                            <strong>Cédula/Nombre</strong>
                        @endif
                        : "{{ request('search') }}"
                    </small>
                </div>
            @endif
        </form>
    </div>

    <!-- Información de resultados -->
    @if($pacientes->count() > 0)
        <div class="alert alert-info">
            Se encontraron <strong>{{ $pacientes->total() }}</strong> paciente(s)
            @if(request()->has('search'))
                para la búsqueda "{{ request('search') }}"
            @endif
        </div>
    @endif

    <!-- Tabla de pacientes -->
    @if($pacientes->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Ingreso</th>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        {{-- <th>Edad</th>
                        <th>Sexo</th> --}}
                        <th>Tlfo.</th>
                        <th>Dir.</th>
                        <th>Email</th>
                        <th>Biopsia</th>    
                        <th>Citología</th>
                        <th>Procedencia</th>
                        <th>Médico</th>
                        <th>Status</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pacientes as $paciente)
                    <tr>
                        <td>{{ $paciente->id }}</td>
                        <td>{{ $paciente->created_at}}</td>
                        <td>{{ $paciente->CI }}</td>
                        <td>{{ $paciente->Nombre }}</td>
                        {{-- <td>{{ $paciente->Edad }} años</td>
                        <td>{{ $paciente->Sexo }}</td> --}}
                        <td>{{ $paciente->Teléfono ?? 'N/A' }}</td>
                        <td>{{ $paciente->Dirección ?? 'N/A' }}</td>
                        {{-- <td>{{ $paciente->procedencia->Codigo ?? 'N/A' }}</td>
                        <td>{{ $paciente->medico->Codigo ?? 'N/A' }}</td> --}}
                        
                        <td>{{ $paciente->mail }}</td>
                        {{-- <td>{{ $paciente->NB }}</td>
                        <td>{{ $paciente->NC }}</td> --}}
                        <td>
                            @if($paciente->NB)
                                <!-- Enlace a biopsia usando el número de biopsia -->
                                <a href="{{ route('biopsias.edit', $paciente->id) }}" 
                                   class="btn btn-outline-primary btn-sm"
                                   title="Formulario-Crear-NB/{{ $paciente->NB }}">
                                    {{ $paciente->NB }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($paciente->status == 1)
                                            <!-- Completado - Solo mostrar información -->
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i> Procesada/{{$paciente->NC}}
                                            </span>
                                        @else
                                    @if($paciente->NC)
                                    <a href="{{ route('citologias.edit', $paciente->id) }}" 
                                    class="btn btn-outline-success btn-sm"
                                    title="Formulario-Crear-NC/{{ $paciente->NC }}">
                                        {{ $paciente->NC }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            {{ $paciente->procedencia->Procedencia ?? $paciente->Procedencia }}
                        </td>
                        <td>
                            {{ $paciente->medico->Codigo ?? $paciente->Codigo }}
                           
                        </td>
                            <td>
                                @if($paciente->status == 0)
                                    <span class="text-warning">
                                                <i class="fas fa-check"></i> Pendiente
                                    </span>
                                    @elseif($paciente->status == 1)
                                    <span class="text-success">
                                                <i class="fas fa-check-circle"></i> Procesada
                                    </span>
                                @endif
                        </td>
                        <td>
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="{{ route('pacientes.show', $paciente->id) }}" 
                                   class="btn btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <a href="{{ route('pacientes.edit', $paciente->id) }}" class="btn btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="{{ route('pacientes.destroy', $paciente->id) }}" 
                                      method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('¿Estás seguro de eliminar este paciente?')"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i> Eliminar
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
                Mostrando {{ $pacientes->firstItem() }} - {{ $pacientes->lastItem() }} de {{ $pacientes->total() }} registros
            </div>
            <div>
                {{ $pacientes->links() }}
            </div>
        </div>
        
    @else
        <div class="text-center py-5">
            <div class="alert alert-warning">
                <i class="fas fa-search fa-2x mb-3"></i>
                <h4>No se encontraron pacientes</h4>
                @if(request()->has('search'))
                    <p>No hay resultados para "{{ request('search') }}" en la búsqueda por 
                    @if(request('tipo') == 'ci')
                        <strong>Cédula/Nombre</strong>
                    @elseif(request('tipo') == 'nb')
                        <strong>Número de Biopsia</strong>
                    @elseif(request('tipo') == 'nc')
                        <strong>Número de Citología</strong>
                    @else
                        <strong>Cédula/Nombre</strong>
                    @endif
                    </p>
                    <a href="{{ route('pacientes.index') }}" class="btn btn-primary">
                        Ver todos los pacientes
                    </a>
                @else
                    <p>No hay pacientes registrados en el sistema.</p>
                    <a href="{{ route('pacientes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Registrar primer paciente
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>


<style>
.frame {
    background-color: #f8f9fa;
    border-radius: 5px;
}
.btn-group-vertical .btn {
    margin-bottom: 2px;
    border-radius: 3px;
}
.badge {
    font-size: 0.85em;
}
.table th {
    background-color: #343a40;
    color: white;
    font-weight: 600;
}
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endsection