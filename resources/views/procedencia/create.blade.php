
@extends('layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus-circle me-2"></i>Nueva Procedencia
        </h1>
        <a href="{{ route('procedencias.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver al Listado
        </a>
    </div>

    <!-- Alertas -->
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
                <i class="fas fa-info-circle me-2"></i>Información de la Procedencia
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('procedencias.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Codigo" class="font-weight-bold">Código *</label>
                            <input type="text" class="form-control @error('Codigo') is-invalid @enderror" 
                                   id="Codigo" name="Codigo" 
                                   value="{{ old('Codigo') }}" 
                                   placeholder="Ingrese el código" required>
                            @error('Codigo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ListaPrecio" class="font-weight-bold">Lista de Precio *</label>
                            <input type="text" step="0.01" class="form-control @error('ListaPrecio') is-invalid @enderror" 
                                   id="ListaPrecio" name="ListaPrecio" 
                                   value="{{ old('ListaPrecio') }}" 
                                   placeholder="0.00" required>
                            @error('ListaPrecio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar Procedencia
                            </button>
                            <a href="{{ route('procedencias.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
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
        
        // Formatear el input de precio
        $('#ListaPrecio').on('blur', function() {
            let value = parseFloat($(this).val());
            if (!isNaN(value)) {
                $(this).val(value.toFixed(2));
            }
        });
    });
</script>
@endsection