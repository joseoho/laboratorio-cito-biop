<!-- resources/views/anexos/index.blade.php -->
@extends('layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-paperclip me-2"></i>Gestión de Anexos
        </h1>
        <a href="{{ route('anexos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Anexo
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
                <i class="fas fa-list me-2"></i>Lista de Anexos
            </h6>
        </div>
        <div class="card-body">
            <!-- Barra de búsqueda -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <form action="{{ route('anexos.index') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Buscar por código..." 
                                   value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                @if(request()->has('search'))
                                    <a href="{{ route('anexos.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-right">
                    <span class="text-muted">
                        Total: {{ $anexos->total() }} anexo(s)
                    </span>
                </div>
            </div>

            <!-- Tabla de anexos -->
            @if($anexos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th width="80">Código</th>
                                <th>Anexo</th>
                                <th width="100">Orden</th>
                                {{-- <th width="120">Acciones</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($anexos as $anexo)
                            <tr>
                                <td>
                                    <strong>{{ $anexo->Cod }}</strong>
                                </td>
                                <td>
                                    <strong>{{ $anexo->Anexo }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong>{{ $anexo->Orden }}</strong>
                                </td>
                                <td>
                                    {{-- <div class="btn-group" role="group">
                                        <a href="{{ route('anexos.edit', $anexo->Cod) }}" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form action="{{ route('anexos.destroy', $anexo->Cod) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('¿Estás seguro de eliminar este anexo?')"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
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
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Mostrando {{ $anexos->firstItem() }} a {{ $anexos->lastItem() }} de {{ $anexos->total() }} registros
                    </div>
                    <div>
                        {{ $anexos->appends(request()->query())->links() }}
                    </div>
                </div>
                
            @else
                <div class="text-center py-5">
                    <i class="fas fa-paperclip fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No se encontraron anexos</h4>
                    <p class="text-muted">
                        @if(request()->has('search'))
                            Intenta ajustar el código de búsqueda
                        @else
                            Comienza agregando el primer anexo
                        @endif
                    </p>
                    <a href="{{ route('anexos.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>Agregar Primer Anexo
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