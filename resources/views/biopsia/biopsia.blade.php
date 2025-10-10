<!-- resources/views/biopsia/form.blade.php -->
@extends('layout.layout')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biopsias - LABORATORIO BECOMARCA</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para íconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        h1, h2 {
            color: #343a40;
            font-weight: bold;
        }

        .frame {
            border: 1px solid #dee2e6;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        label {
            color: #495057;
            font-weight: 500;
        }

        .form-control {
            border-radius: 4px;
            border: 1px solid #ced4da;
        }

        .btn-custom {
            background-color: #28a745;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
        }

        .btn-custom:hover {
            background-color: #218838;
        }

        textarea {
            resize: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>LABORATORIO DE ANATOMIA PATOLOGICA "Dr. Jose R. Becker Reyes"</h1>
        <h2>Biopsias</h2>

        <!-- Frame de Biopsia -->
        <div class="frame">
            <h3>Biopsias</h3>
            <div class="row">
                <div class="col-md-3">
                    <label for="recibo">Recibo:</label>
                    <input type="text" value="{{ $paciente->id }}" id="recibo" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="ci">C.I No.:</label>
                    <input type="text" value="{{ $paciente->cedula }}" id="ci" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="noBiopsia">No. Biopsia:</label>
                    <input type="text" value="{{ $paciente->biopsia }}" id="noBiopsia" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="nombres">Nombres:</label>
                    <input type="text" value="{{ $paciente->nombre }}" id="nombres" class="form-control" readonly>
                </div>
            </div>
        </div>

        <form action="{{ route('biopsia.guardar', $paciente->biopsia) }}" method="POST">
            @csrf

            <!-- Frame de Identificación de la muestra -->
            <div class="frame">
                <h3>Identificación de la muestra</h3>
                <div class="row">
                    <div class="col-md-6">
                        <label for="fechaRecib">Fecha Recib.:</label>
                        <input type="date" name="fecha_recibido" id="fechaRecib" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="fechaEnvio">Fecha Envio:</label>
                        <input type="date" name="fecha_envio" id="fechaEnvio" class="form-control">
                    </div>
                </div>
            </div>

            <!-- Frame de Material Remitido y Diagnóstico clínico -->
            <div class="frame">
                <div class="row">
                    <div class="col-md-6">
                        <label for="materialRemitido">Material Remitido:</label>
                        <input type="text" name="material_remitido" id="materialRemitido" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="diagnosticoClinico">Diagnóstico clínico:</label>
                        <input type="text" name="diagnostico_clinico" id="diagnosticoClinico" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="sitioLesion">Sitio de la Lesión:</label>
                        <input type="text" name="sitio_lesion" id="sitioLesion" class="form-control">
                    </div>
                </div>
            </div>

            <div class="frame">
                <div class="row">
                    <div class="col-md-6">
                        <label for="descripcionMacroscopica">Descripción Macroscópica:</label>
                        <textarea name="descripcion_macroscopica" id="descripcionMacroscopica" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="plantillaMacroscopica">Plantilla:</label>
                            <div class="input-group">
                                <select name="plantilla_macroscopica" id="plantillaMacroscopica" class="form-control">
                                    <option value="">Seleccionar plantilla</option>
                                    <option value="1">Plantilla 1</option>
                                    <option value="2">Plantilla 2</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-custom" onclick="guardarPlantilla('macroscopica')">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Frame de Descripción Microscópica -->
            <div class="frame">
                <div class="row">
                    <div class="col-md-6">
                        <label for="descripcionMicroscopica">Descripción Microscópica:</label>
                        <textarea name="descripcion_microscopica" id="descripcionMicroscopica" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="plantillaMicroscopica">Plantilla:</label>
                            <div class="input-group">
                                <select name="plantilla_microscopica" id="plantillaMicroscopica" class="form-control">
                                    <option value="">Seleccionar plantilla</option>
                                    <option value="1">Plantilla 1</option>
                                    <option value="2">Plantilla 2</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-custom" onclick="guardarPlantilla('microscopica')">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Frame de Diagnóstico Microscópico -->
            <div class="frame">
                <div class="row">
                    <div class="col-md-6">
                        <label for="diagnosticoMicroscopico">Diagnóstico Microscópico:</label>
                        <textarea name="diagnostico_microscopico" id="diagnosticoMicroscopico" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="plantillaDiagnostico">Plantilla:</label>
                            <div class="input-group">
                                <select name="plantilla_diagnostico" id="plantillaDiagnostico" class="form-control">
                                    <option value="">Seleccionar plantilla</option>
                                    <option value="1">Plantilla 1</option>
                                    <option value="2">Plantilla 2</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-custom" onclick="guardarPlantilla('diagnostico')">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Biopsia Completa
                </button>
                <a href="{{ route('pacientes.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function guardarPlantilla(tipo) {
            // Lógica para guardar plantillas (puede ser via AJAX)
            alert('Función de guardar plantilla ' + tipo + ' será implementada');
        }
    </script>
</body>
</html>
@endsection