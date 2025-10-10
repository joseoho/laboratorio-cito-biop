    <div class="card shadow">
        <div class="card-body">
           
                <div class="row">
                    <!-- Campo Nombre -->
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label required">Nombre de la Finca</label>
                        <input type="text" 
                               class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" 
                               name="nombre" 
                               value="{{ old('nombre') }}"
                               required
                               maxlength="100">
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Ej: Hacienda Santa María</small>
                    </div>

                    <!-- Campo Ubicación -->
                    <div class="col-md-6 mb-3">
                        <label for="ubicacion" class="form-label">Ubicación</label>
                        <input type="text" 
                               class="form-control @error('ubicacion') is-invalid @enderror" 
                               id="ubicacion" 
                               name="ubicacion" 
                               value="{{ old('ubicacion') }}"
                               maxlength="255">
                        @error('ubicacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Ej: Carretera Nacional Km 12</small>
                    </div>

                    <!-- Campo Área -->
                    <div class="col-md-4 mb-3">
                        <label for="area" class="form-label">Área (hectáreas)</label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control @error('area') is-invalid @enderror" 
                                   id="area" 
                                   name="area" 
                                   value="{{ old('area') }}"
                                   step="0.01"
                                   min="0">
                            <span class="input-group-text">ha</span>
                            @error('area')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Campo Descripción -->
                    <div class="col-12 mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="descripcion" 
                                  name="descripcion" 
                                  rows="3">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="row mt-4">
                    <div class="col-12 text-end">
                        <a href="{{ route('fincas.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Finca
                        </button>
                    </div>
                </div>
            
        </div>
    </div>
</div>


@push('scripts')
<script>
    // Validación del formulario antes de enviar
    document.getElementById('form-crear-finca').addEventListener('submit', function(e) {
        const nombre = document.getElementById('nombre').value.trim();
        
        if (!nombre) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Campo requerido',
                text: 'El nombre de la finca es obligatorio',
                confirmButtonColor: '#3085d6'
            });
            document.getElementById('nombre').focus();
        }
    });

    // Opcional: Máscaras o formatos
    // Ejemplo para formatear el área si es necesario
    document.getElementById('area')?.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
</script>
@endpush

@push('styles')
<style>
    .required:after {
        content: " *";
        color: #dc3545;
    }
    /* Estilos adicionales para mejorar la UX */
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endpush