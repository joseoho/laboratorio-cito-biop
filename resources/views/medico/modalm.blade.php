<!-- Modal para Médico - Bootstrap 5 Optimizado -->
<div class="modal fade" id="modalMedico" tabindex="-1" aria-labelledby="modalMedicoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMedicoLabel">Nuevo Médico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formMedico">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="medico_codigo" class="form-label">Código: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="medico_codigo" name="Codigo" required 
                               placeholder="Ingrese el código único">
                    </div>
                    <div class="mb-3">
                        <label for="medico_nombre" class="form-label">Nombre: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="medico_nombre" name="Nombre" required 
                               placeholder="Ingrese el nombre del médico">
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="telefono" name="telefono" required 
                               placeholder="Ingrese El Teléfono">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarMedico">
                        <span id="spinnerMedico" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Guardar Médico
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>