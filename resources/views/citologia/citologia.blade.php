@extends('layouts.layout')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col">
            <h1>LABORATORIO DE ANATOMIA PATOLOGICA "Dr. Jose R. Becker Reyes"</h1>
            <h2>Citologías, Registrar</h2>
        </div>
        <div class="col text-right">
            <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">Volver a Pacientes</a>
        </div>
    </div>

    <!-- Formulario de Citología -->
     @if ($errors->any())
    <div class="alert alert-danger">
        <h5>Por favor, corrige los siguientes errores:</h5>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <form action="{{ route('citologias.store') }}" method="POST" id="formBiopsia">
        @csrf
        
        <!-- Campo oculto para el ID del paciente -->
        <input type="hidden" name="IdPaciente" value="{{ $paciente->id}}">

        <!-- Frame de Citología -->
        <div class="frame mb-4 p-3 border rounded">
            <h3>Datos del Paciente</h3>
            <div class="row">
                <div class="col-md-3">
                    <label for="recibo" class="form-label">Recibo:</label>
                    <input type="text" value="{{ $paciente->id }}" name="id_paciente" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="ci" class="form-label">C.I No.:</label>
                    <input type="text" value="{{ $paciente->CI }}" name="ci" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="noCitologia" class="form-label">No. Citología:</label>
                    <input type="text" value="{{ $paciente->NC }}" name="nc" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="nombres" class="form-label">Nombres:</label>
                    <input type="text" value="{{ $paciente->Nombre }}" name="nombre" class="form-control" readonly>
                </div>
            </div>
        </div>

        <!-- Frame de Identificación de la muestra -->
        <div class="frame mb-4 p-3 border rounded">
            <h3>Identificación de la muestra</h3>
            <div class="row">
                <div class="col-md-6">
                    <label for="FechaRecibido" class="form-label">Fecha Recibido:</label>
                    <input type="date" id="FechaRecibido" name="FechaRecibido" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="FechaEnvio" class="form-label">Fecha Envio:</label>
                    <input type="date" id="FechaEnvio" name="FechaEnvio" class="form-control">
                </div>
            </div>
        </div>

        <!-- Frame de Material Remitido y Diagnóstico clínico -->
        <div class="frame mb-4 p-3 border rounded">
            <div class="row">
                <div class="col-md-6">
                    <label for="MaterialRemitido" class="form-label">Material Remitido:</label>
                    <input type="text" id="MaterialRemitido" name="MaterialRemitido" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="DiagnosticoClinico" class="form-label">Diagnóstico clínico:</label>
                    <input type="text" id="DiagnosticoClinico" name="DiagnosticoClinico" class="form-control">
                </div>
            </div>
        </div>

        <!-- Frame de Calidad de la muestra -->
        <div class="frame mb-4 p-3 border rounded">
            <label for="Calidad" class="form-label">Calidad de la muestra:</label>
            <textarea id="Calidad" name="Calidad" class="form-control" rows="3"></textarea>
        </div>

        <!-- Frame de Descripción Macroscópica -->
        <div class="frame mb-4 p-3 border rounded">
            <label for="DescripcionMacroscopica" class="form-label">Descripción Macroscópica:</label>
            <textarea id="DescripcionMacroscopica" name="DescripcionMacroscopica" class="form-control" rows="3"></textarea>
        </div>

        <!-- Frame de Descripción Citología -->
        <div class="frame mb-4 p-3 border rounded">
            <h3>Descripción Citología</h3>
            <div class="row">
                        <label for="Cod" class="form-label">Cod:</label>
                        <select id="cod" name="cod" class="form-control">
                            <option value="">Seleccione un código</option>
                            @foreach($diagcitos as $diagcito)
                                <option value="{{ $diagcito->Cod }}" 
                                        data-descripcion="{{ $diagcito->Descripcion }}"
                                        data-diagnostico="{{ $diagcito->Diagnostico }}">
                                    {{ $diagcito->Cod }} - {{ $diagcito->Descripcion }}
                                </option>
                            @endforeach
                        </select>    
                        <label for="anexo" class="form-label">Anexo:</label>
                              <select id="anexo" name="anexo" class="form-control"> class="form-control" required>
                                   <option value="">Seleccione...</option>
                                           @foreach($anexos as $anexo)
                                            <option value="{{ $anexo->Cod }}">
                                                {{ $anexo->Anexo}}- {{ $anexo->Cod }}
                                            </option>
                                           @endforeach
                              </select>
                        </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <label for="DescripcionCitologica" class="form-label">Descripción Citología:</label>
                    <textarea id="DescripcionCitologica" name="DescripcionCitologica" class="form-control" rows="3"></textarea>
                </div>
            </div>
        </div>

        <!-- Frame de Diagnóstico Citología -->
        <div class="frame mb-4 p-3 border rounded">
            <label for="DiagnosticoCitologico" class="form-label">Diagnóstico Citología:</label>
            <textarea id="DiagnosticoCitologico" name="DiagnosticoCitologico" class="form-control" rows="3"></textarea>
        </div>

        <!-- Frame de Notas -->
        <div class="frame mb-4 p-3 border rounded">
            <label for="Notas" class="form-label">Notas:</label>
            <textarea id="Notas" name="Notas" class="form-control" rows="3"></textarea>
        </div>

        <!-- Frame de Índice de valoración Hormonal -->
        <div class="frame mb-4 p-3 border rounded">
            <h3>Índice de valoración Hormonal</h3>
            <div class="row">
                <div class="col-md-8">
                    <!-- Superficiales -->
                    <div class="form-group row">
                        <label for="Superficiales" class="col-sm-3 col-form-label">Superficiales:</label>
                        <div class="col-sm-2">
                            <input type="number" id="Superficiales" name="Superficiales" class="form-control" value="0" maxlength="5">
                        </div>
                    </div>
                    <!-- Intermedias -->
                    <div class="form-group row">
                        <label for="Intermedias" class="col-sm-3 col-form-label">Intermedias:</label>
                        <div class="col-sm-2">
                            <input type="number" id="Intermedias" name="Intermedias" class="form-control" value="0" maxlength="5">
                        </div>
                    </div>
                    <!-- Parabasales -->
                    <div class="form-group row">
                        <label for="Parabasales" class="col-sm-3 col-form-label">Parabasales:</label>
                        <div class="col-sm-2">
                            <input type="number" id="Parabasales" name="Parabasales" class="form-control" value="0" maxlength="5">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="Conclusion" class="form-label">Conclusión:</label>
                    <textarea id="Conclusion" name="Conclusion" class="form-control" rows="5"></textarea>
                </div>
            </div>
        </div>

        <!-- Frame final -->
        <div class="frame mb-4 p-3 border rounded">
            <div class="row">
                <div class="col-md-6">
                    <label for="MedicoPatologo1" class="form-label">Médico Patólogo 1:</label>
                    <select id="MedicoPatologo1" name="MedicoPatologo1" class="form-control">
                        <option value="">Seleccione médico...</option>
                        <option value="Dr. Juan Carlos Becker">Dr. Juan Carlos Becker</option>
                        <option value="Dr. Claudio Arrechedera">Dr. Claudio Arrechedera</option>
                        <option value="Cit. Ilia C. Molina">Cit. Ilia C. Molina</option>
                        <option value="Cit. Johaiza Contreras">Cit. Johaiza Contreras</option>
                        <option value="Cit. Kandy Contreras">Cit. Kandy Contreras</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="MedicoPatologo2" class="form-label">Médico Patólogo 2:</label>
                    <select id="MedicoPatologo2" name="MedicoPatologo2" class="form-control">
                        <option value="">Seleccione médico...</option>
                        <option value="Dr. Juan Carlos Becker">Dr. Juan Carlos Becker</option>
                        <option value="Dr. Claudio Arrechedera">Dr. Claudio Arrechedera</option>
                        <option value="Cit. Ilia C. Molina">Cit. Ilia C. Molina</option>
                        <option value="Cit. Johaiza Contreras">Cit. Johaiza Contreras</option>
                        <option value="Cit. Kandy Contreras">Cit. Kandy Contreras</option>
                    </select>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Citología
                    </button>
                    <button type="button" class="btn btn-success">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.frame {
    background-color: #f8f9fa;
    border-radius: 5px;
}
</style>
@endsection
<script src="{{ asset('assets/js/llenadiagCito.js') }}"></script>
<script src="{{ asset('assets/js/llenaAnexo.js') }}"></script>