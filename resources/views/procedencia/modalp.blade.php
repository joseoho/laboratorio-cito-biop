<!-- Modal para Procedencia - Bootstrap 5 Optimizado -->
<div class="modal fade" id="modalProcedencia" tabindex="-1" aria-labelledby="modalProcedenciaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProcedenciaLabel">Nueva Procedencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formProcedencia">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="procedencia_codigo" class="form-label">Código: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="procedencia_codigo" name="Codigo" required 
                               placeholder="Ingrese el código único">
                    </div>
                    <div class="mb-3">
                        <label for="procedencia_nombre" class="form-label">Nombre: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="procedencia_nombre" name="Nombre" required 
                               placeholder="Ingrese el nombre de la procedencia">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarProcedencia">
                        <span id="spinnerProcedencia" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Guardar Procedencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>