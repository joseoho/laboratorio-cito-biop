
@extends('layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus me-2"></i>Agregar Nuevo Médico
        </h1>
        <a href="{{ route('medicos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver a la lista
        </a>
    </div>

    <!-- Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-info-circle me-2"></i>Información del Médico
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('medicos.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <!-- Código -->
                    <div class="col-md-6 mb-3">
                        <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('codigo') is-invalid @enderror" 
                               id="codigo" 
                               name="codigo" 
                               value="{{ old('codigo') }}" 
                               placeholder="Ingrese el código del médico Nombre"
                               required>
                        @error('codigo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nombre -->
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" 
                               name="nombre" 
                               value="{{ old('nombre') }}" 
                               placeholder="Ingrese el nombre completo"
                               required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <!-- Especialidad -->
                    {{-- <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" 
                               class="form-control @error('telefono') is-invalid @enderror" 
                               id="especialidad" 
                               name="especialidad" 
                               value="{{ old('telefono') }}" 
                               placeholder="Ej: Ginecología, Patología, etc.">
                        @error('especialidad')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div> --}}

                    <!-- Teléfono -->
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" 
                               class="form-control @error('telefono') is-invalid @enderror" 
                               id="telefono" 
                               name="telefono" 
                               value="{{ old('telefono') }}" 
                               placeholder="Ingrese el número de teléfono">
                        @error('telefono')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- <div class="row">
                    <!-- Email -->
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Ingrese el correo electrónico">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Estado -->
                    <div class="col-md-6 mb-3">
                        <label for="activo" class="form-label">Estado</label>
                        <select class="form-control @error('activo') is-invalid @enderror" 
                                id="activo" 
                                name="activo">
                            <option value="1" {{ old('activo', 1) == 1 ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('activo') == 0 ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('activo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                  id="observaciones" 
                                  name="observaciones" 
                                  rows="3" 
                                  placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div> --}}

                <!-- Botones -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('medicos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar Médico
                            </button>
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
    // Script para auto-generar código si es necesario
    $(document).ready(function() {
        // Ejemplo: Auto-generar código basado en el nombre
        $('#nombre').on('blur', function() {
            if (!$('#codigo').val()) {
                const nombre = $(this).val();
                if (nombre) {
                    // Generar código con las primeras letras del nombre
                    const codigo = nombre.split(' ')
                        .map(word => word.charAt(0))
                        .join('')
                        .toUpperCase();
                    $('#codigo').val(codigo);
                }
            }
        });
    });
</script>
@endsection