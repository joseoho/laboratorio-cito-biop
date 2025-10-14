
@extends('layouts.layout')

@section('content')
<div class="frame">
            <h3>Buscar</h3>
            <div class="input-group">
                <input type="text" id ="valorBusqueda" class="form-control" placeholder="Ingrese su búsqueda">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" id="btnci/nom" onclick="buscarPorCI()" type="button">CI/NOM</button>
                    <button class="btn btn-outline-secondary" id="btnrb" type="button">RB</button>
                    <button class="btn btn-outline-secondary" id="btnnb" onclick="buscarPorNB()"type="button">NB</button>
                    <button class="btn btn-outline-secondary" id="btnnc" onclick="buscarPorNC()" type="button">NC</button>
                </div>
            </div>
        </div>
 <form action="{{ route('pacientes.store') }}" method="POST" id="miFormulario">
    @csrf
    <!-- Frame 2: Identificación -->
    <div class="frame mb-4 p-3 border rounded">
        <h3 class="mb-4">Identificación</h3>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="fechaActual" class="form-label">Fecha de Ingreso:</label>
                <input type="text" value="<?php echo date('Y-m-d'); ?>" id="fechaActual" class="form-control" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label for="cedula" class="form-label">Cédula Número:</label>
                <input type="text" id="cedula" name="cedula" class="form-control" required>
                <div class="invalid-feedback">Por favor ingrese su cédula.</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nombre" class="form-label">Nombres:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required>
                
            </div>
            <div class="col-md-6 mb-3">
                <label for="sexo" class="form-label">Sexo:</label>
                <select id="sexo" name="sexo" class="form-control" required>
                    <option value="">Seleccione...</option>
                    <option value="M">M</option>
                    <option value="F">F</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="direccion" class="form-label">Dirección:</label>
                <input type="text" id="direccion" name="direccion" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label for="telefono" class="form-label">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" class="form-control" title="10 dígitos">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="edad" class="form-label">Edad:</label>
                <input type="number" id="edad" name="edad" class="form-control" min="1" max="120">
            </div>
            <div class="col-md-6 mb-3">
                <label for="procedencia" class="form-label">Procedencia:</label>
                <div class="input-group">
                    <select id="procedencia" name="procedencia_id" class="form-control" required>
                        <option value="">Seleccione...</option>
                        @foreach($procedencias as $procedencia)
                            <option value="{{ $procedencia->Codigo }}">{{ $procedencia->Codigo }}</option>
                        @endforeach
                    </select>
                   
                        <a href="{{ route('procedencias.create') }}" 
                        class="btn btn-outline-secondary"
                        title="Agregar nueva procedencia">
                            <i class="fas fa-plus"></i>
                        </a>
                </div>
            </div>
        </div>
            <div class="col-md-6 mb-3">
                <label for="medicos" class="form-label">Médicos:</label>
                <div class="input-group">
                    <select id="medicos" name="medico_id" class="form-control" required>
                        <option value="">Seleccione...</option>
                        @foreach($medicos as $medico)
                            <option value="{{ $medico->Codigo }}">{{ $medico->Codigo }} 
                              {{--  @if($medico->especialidad)
                                    ({{ $medico->especialidad }})
                                @endif --}}
                            </option>
                        @endforeach
                    </select>
                  
                        <a href="{{ route('medicos.create') }}" 
                            class="btn btn-outline-secondary"
                            title="Agregar nuevo médico">
                            <i class="fas fa-plus"></i>
                        </a>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="Mail" class="form-label">Correo Electrónico:</label>
                <input type="email" id="Mail" name="Mail" class="form-control" required>
                <div class="invalid-feedback">Ingrese un correo válido.</div>
            </div>
        </div>


        <!-- Frame: Citologías y Biopsias -->
<div class="frame mb-4 p-3 border rounded">
    <h3 class="mb-4">Biopsias y Citologías</h3>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="NB" class="form-label">Número de Biopsia:</label>
            <div class="input-group">
                <input type="number" id="NB" name="NB" class="form-control" min="1">
                {{-- <button type="submit" name="btnBiopsia" value="1" class="btn btn-outline-primary">
                    Biopsia
                </button> --}}
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label for="NC" class="form-label">Número de Citología:</label>
            <div class="input-group">
                <input type="number" id="NC" name="NC" class="form-control" min="1">
                {{-- <button type="submit" name="btnCitologia" value="1" class="btn btn-outline-success">
                    Citología
                </button> --}}
            </div>
        </div>
    </div>


            <!-- Botones de guardado normales -->
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Guardar Paciente</button>
                    <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </div> 
            </form>
</div>


@endsection