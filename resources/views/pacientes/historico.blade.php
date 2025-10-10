@extends('layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h3><i class="fas fa-history"></i> Histórico de Animales Eliminados</h3>
                <a href="{{ route('animales.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Volver a Animales
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <div class="mb-3">
                <button type="button" onclick="window.print()" class="btn btn-success">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Raza</th>
                            <th>Sexo</th>
                            <th>Lote</th>
                            <th>Fecha Eliminación</th>
                            <th>Eliminado por</th>
                            <th>Motivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($animales as $animal)
                        <tr>
                            <td>{{ $animal->codigo }}</td>
                            <td>{{ $animal->raza }}</td>
                            <td>{{ $animal->sexo }}</td>
                            <td>{{ $animal->lote->nombre ?? 'N/A' }}</td>
                            <td>{{ $animal->deleted_at}}</td>
                            <td>{{ $animal->historial->first()->usuario->name ?? 'Sistema' }}</td>
                            <td>{{ $animal->historial->first()->motivo ?? 'No especificado' }}</td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                    data-bs-target="#detalleModal{{ $animal->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                {{-- <form action="{{ route('historico-animales.restore', $animal->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success" title="Restaurar">
                                        <i class="fas fa-trash-restore"></i>
                                    </button>
                                </form>--}}
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#confirmarEliminacion{{ $animal->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No hay animales eliminados en el histórico</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $animales->links() }}
        </div>
    </div>
</div>

<!-- Modales para detalles -->
@foreach($animales as $animal)
<div class="modal fade" id="detalleModal{{ $animal->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalle de Animal Eliminado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Código:</strong> {{ $animal->codigo }}</p>
                        <p><strong>Raza:</strong> {{ $animal->raza }}</p>
                        <p><strong>Sexo:</strong> {{ $animal->sexo }}</p>
                        <p><strong>Fecha Nacimiento:</strong> 
                            @if($animal->fecha_nacimiento)
                                {{ $animal->fecha_nacimiento }}
                                ({{ $animal->fecha_nacimiento }} años)
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Lote:</strong> {{ $animal->lote->nombre ?? 'N/A' }}</p>
                        <p><strong>Finca:</strong> {{ $animal->lote->finca->nombre ?? 'N/A' }}</p>
                        <p><strong>Peso Inicial:</strong> 
                            {{ $animal->peso_inicial ? number_format($animal->peso_inicial, 2).' kg' : 'N/A' }}
                        </p>
                        <p><strong>Estado al eliminar:</strong> 
                            <span class="badge bg-{{ [
                                'Activo' => 'success',
                                'Vendido' => 'info',
                                'Enfermo' => 'warning',
                                'Muerto' => 'danger'
                            ][$animal->estado] ?? 'secondary' }}">
                                {{ $animal->estado }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="mt-3">
                    <h5>Observaciones:</h5>
                    <p>{{ $animal->observaciones ?? 'Ninguna' }}</p>
                </div>
                <div class="mt-3">
                    <h5>Información de Eliminación:</h5>
                    <p><strong>Eliminado por:</strong> {{ $animal->historial->first()->usuario->name ?? 'Sistema' }}</p>
                    <p><strong>Motivo:</strong> {{ $animal->historial->first()->motivo ?? 'No especificado' }}</p>
                    <p><strong>Fecha eliminación:</strong> {{ $animal->deleted_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación permanente -->
<div class="modal fade" id="confirmarEliminacion{{ $animal->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación Permanente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de eliminar permanentemente este animal? Esta acción no se puede deshacer.</p>
                <p class="fw-bold">Animal: {{ $animal->codigo }} - {{ $animal->raza }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                {{-- <form action="{{ route('historico-animales.destroy', $animal->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Permanentemente</button>
                </form> --}}
            </div>
        </div>
    </div>
</div>
@endforeach


<script>
// Si necesitas funcionalidad JavaScript adicional
$(document).ready(function() {
    // Puedes agregar interacciones aquí si es necesario
});
</script>
@endsection