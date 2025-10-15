{{-- resources/views/citologias/imprimir.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Citología - {{ $citologia->paciente->NC ?? 'N/A' }}</title>
    <style>
        /* Estilos para impresión */
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-family: 'Arial', sans-serif;
                font-size: 12px;
                color: #000;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-after: always;
            }
            
            .header, .footer {
                position: fixed;
                left: 0;
                right: 0;
                background-color: white;
            }
            
            .header {
                top: 0;
                height: 120px;
                border-bottom: 2px solid #333;
            }
            
            .footer {
                bottom: 0;
                height: 50px;
                border-top: 1px solid #333;
                text-align: center;
                font-size: 10px;
            }
            
            .content {
                margin-top: 130px;
                margin-bottom: 60px;
            }
        }

        /* Estilos para vista previa */
        @media screen {
            body {
                font-family: 'Arial', sans-serif;
                font-size: 14px;
                margin: 20px;
                background-color: #f8f9fa;
            }
            
            .print-container {
                max-width: 21cm;
                min-height: 29.7cm;
                margin: 0 auto;
                background: white;
                padding: 1cm;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            
            .header {
                border-bottom: 2px solid #333;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            
            .footer {
                border-top: 1px solid #333;
                padding-top: 10px;
                margin-top: 20px;
                text-align: center;
                font-size: 12px;
            }
        }

        /* Estilos generales */
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .logo-container {
            text-align: right;
        }
        
        .logo {
            max-width: 80px;
            max-height: 80px;
        }
        
        .lab-info {
            flex: 1;
        }
        
        .lab-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .lab-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .report-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-decoration: underline;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px 10px;
            margin-bottom: 10px;
            border-left: 4px solid #333;
        }
        
        .patient-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .info-item {
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        .text-area-content {
            border: 1px solid #ddd;
            padding: 10px;
            min-height: 60px;
            margin-bottom: 10px;
            white-space: pre-wrap;
        }
        
        .signature-area {
            margin-top: 40px;
            display: flex;
            justify-content: space-around;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            text-align: center;
            padding-top: 5px;
        }
        
        .hormonal-index {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .index-item {
            text-align: center;
        }
        
        .index-value {
            font-size: 16px;
            font-weight: bold;
        }
        
        .controls {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <!-- Controles para vista previa -->
    <div class="controls no-print">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Imprimir
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            ❌ Cerrar
        </button>
        <button onclick="window.history.back()" class="btn btn-info">
            ↩️ Volver
        </button>
    </div>

    <div class="print-container">
        <!-- Header con espacio para imagen -->
        <div class="header">
            <div class="header-content">
                <div class="lab-info">
                    <div class="lab-name">
                        LABORATORIO DE ANATOMIA PATOLOGICA
                    </div>
                    <div class="lab-title">
                        "Dr. Jose R. Becker Reyes"
                    </div>
                    <div>
                        <strong>Dirección:</strong> Urb. San Inés, Hotel Valle Santiango, CENTROMED, San Cristóbal, Edo Táchira
                    </div>
                    <div>
                        <strong>Teléfono:</strong> 0276-3410380
                    </div>
                    <div>
                        <strong>Email:</strong> becomarca@gmail.com
                    </div>
                </div>
                <div class="logo-container">
                    <!-- Espacio para logo/imagen -->
                    <div class="logo-placeholder" style="width: 80px; height: 80px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666;">
                        <img src="{{ asset('images/logo.png') }}" class="logo" alt="Logo Laboratorio"><br>80x80px
                    </div>
                    <!-- Si tienes una imagen: -->
                    <!-- <img src="{{ asset('images/logo.png') }}" class="logo" alt="Logo"> -->
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <!-- Título del reporte -->
            <div class="report-title">
                INFORME DE CITOLOGÍA
            </div>

            <!-- Información del Paciente -->
            <div class="section">
                <div class="section-title">INFORMACIÓN DEL PACIENTE</div>
                <div class="patient-info">
                    <div class="info-item">
                        <span class="info-label">No. Citología:</span> 
                        {{ $citologia->paciente->NC ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Cédula:</span> 
                        {{ $citologia->paciente->CI ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Recibo:</span> 
                        {{ $citologia->paciente->id ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nombre:</span> 
                        {{ $citologia->paciente->Nombre ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Edad:</span> 
                        {{ $citologia->paciente->Edad ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Sexo:</span> 
                        {{ $citologia->paciente->Sexo ?? 'N/A' }}
                    </div>
                
                    <div class="info-item">
                        <span class="info-label">Medico:</span> 
                        {{ $citologia->paciente->Dr ?? 'N/A' }}
                    </div>
                <div class="info-item">
                        <span class="info-label">Fecha Recepción:</span> 
                        @if($citologia->FechaRecibido)
                            {{ \Carbon\Carbon::parse($citologia->FechaRecibido)->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha Envío:</span> 
                        @if($citologia->FechaEnvio)
                            {{ \Carbon\Carbon::parse($citologia->FechaEnvio)->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información de la Muestra -->
            <div class="section">
                <div class="section-title">INFORMACIÓN DE LA MUESTRA</div>
                <div class="patient-info">
                    
                    <div class="info-item">
                        <span class="info-label">Material Remitido:</span> 
                        {{ $citologia->MaterialRemitido ?? 'N/A' }}
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-label">Diagnóstico Clínico:</span> 
                    {{ $citologia->DiagnosticoClinico ?? 'N/A' }}
                </div>

                 <div class="info-item">
                    <span class="info-label">Calidad de la Muestra:</span> 
                    {{ $citologia->Calidad ?? 'N/A' }}
                </div>
            </div>

            <!-- Descripción Macroscópica -->
            @if($citologia->DescripcionMacroscopica)
            <div class="section">
                <div class="section-title">DESCRIPCIÓN MACROSCÓPICA</div>
                <div class="text-area-content">
                    {{ $citologia->DescripcionMacroscopica }}
                </div>
            </div>
            @endif

            <!-- Descripción Citológica -->
            @if($citologia->DescripcionCitologica)
            <div class="section">
                <div class="section-title">DESCRIPCIÓN CITOLÓGICA</div>
                <div class="text-area-content">
                    {{ $citologia->DescripcionCitologica }}
                </div>
            </div>
            @endif

            <!-- Diagnóstico Citológico -->
            @if($citologia->DiagnosticoCitologico)
            <div class="section">
                <div class="section-title">DIAGNÓSTICO CITOLÓGICO</div>
                <div class="text-area-content">
                    {{ $citologia->DiagnosticoCitologico }}
                </div>
            </div>
            @endif

            <!-- Índice Hormonal -->
            @if($citologia->Superficiales || $citologia->Intermedias || $citologia->Parabasales)
            <div class="section">
                <div class="section-title">ÍNDICE DE VALORACIÓN HORMONAL</div>
                <div class="hormonal-index">
                    <div class="index-item">
                        <div class="info-label">Superficiales</div>
                        <div class="index-value">{{ $citologia->Superficiales ?? 0 }}</div>
                    </div>
                    <div class="index-item">
                        <div class="info-label">Intermedias</div>
                        <div class="index-value">{{ $citologia->Intermedias ?? 0 }}</div>
                    </div>
                    <div class="index-item">
                        <div class="info-label">Parabasales</div>
                        <div class="index-value">{{ $citologia->Parabasales ?? 0 }}</div>
                    </div>
                </div>
                @if($citologia->Conclusion)
                <div class="text-area-content">
                    <strong>Conclusión:</strong> {{ $citologia->Conclusion }}
                </div>
                @endif
            </div>
            @endif

            <!-- Notas -->
            @if($citologia->Notas)
            <div class="section">
                <div class="section-title">NOTAS</div>
                <div class="text-area-content">
                    {{ $citologia->Notas }}
                </div>
            </div>
            @endif

            <!-- Firmas -->
            <div class="signature-area">
                <div class="signature-line">
                    {{ $citologia->MedicoPatologo1 ?? '____________________' }}
                    <br>
                    <small>Médico Patólogo 1</small>
                </div>
                <div class="signature-line">
                    {{ $citologia->MedicoPatologo2 ?? '____________________' }}
                    <br>
                    <small>Médico Patólogo 2</small>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>
                Urb. San Inés, Hotel Valle Santiango, CENTROMED, San Cristóbal, Edo Táchira,
                Tel: 0276-3410380,
                Email: becomarca@gmail.com,
                <br>Rif: J-29890247-7
                Fecha de impresión: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
                {{$citologia->paciente->id}}<hr>
                Se entrega informe original, quedando copia fiel y exacta en los archivos de este laboratorio, el cual no se hace responsable por posibles tachaduras, enmendaruas, copias o escaneos realizados.
            </div>
        </div>
    </div>

    <script>
        // Auto-imprimir si se desea
        // window.onload = function() {
        //     window.print();
        // };
        
        // Cerrar ventana después de imprimir
        window.onafterprint = function() {
            // window.close();
        };
    </script>
</body>
</html>