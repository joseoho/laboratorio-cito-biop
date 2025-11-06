{{-- resources/views/citologias/imprimir.blade.php --}}
{{-- resources/views/biopsias/imprimir.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Biopsia - {{ $biopsia->id ?? 'N/A' }}</title>
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
        
        .diagnosis-box {
            background-color: #fffacd;
            border: 2px solid #ff6b6b;
            padding: 15px;
            margin: 15px 0;
            font-weight: bold;
            font-size: 14px;
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
        
        .controls {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e9ecef;
        }
        
        .photo-indicator {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            margin-left: 5px;
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
                BIOPSIA
            </div>

            <!-- Información General -->
            <div class="section">
                <div class="section-title">INFORMACIÓN GENERAL</div>
                <div class="patient-info">
                    <div class="info-item">
                        <span class="info-label">No. Biopsia:</span> 
                        {{ $biopsia->paciente->NB ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nombre:</span> 
                        {{ $biopsia->paciente->Nombre ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Procedencia:</span> 
                        {{ $biopsia->paciente->Procedencia ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Dr(a):</span> 
                        {{ $biopsia->paciente->Dr ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Recibida:</span> 
                        @if($biopsia->FechaRecibido)
                            {{ \Carbon\Carbon::parse($biopsia->FechaRecibido)->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="info-item">
                        <span class="info-label">Enviada:</span> 
                        @if($biopsia->FechaEnvio)
                            {{ \Carbon\Carbon::parse($biopsia->FechaEnvio)->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </div>
                    
                    {{-- <div class="info-item">
                        <span class="info-label">Fotos Adjuntas:</span> 
                        @if($biopsia->Foto_1 || $biopsia->Foto_2 || $biopsia->Foto_3)
                            Sí 
                            @if($biopsia->Foto_1)<span class="photo-indicator">1</span>@endif
                            @if($biopsia->Foto_2)<span class="photo-indicator">2</span>@endif
                            @if($biopsia->Foto_3)<span class="photo-indicator">3</span>@endif
                        @else
                            No
                        @endif
                    </div> --}}
                </div>
            </div>

            <!-- Diagnóstico Clínico -->
             <div class="info-item">
                        <span class="info-label">Material Remitido:</span> 
                        {{ $biopsia->MaterialRemitido ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Diag. Clinico:</span> 
                            {{ $biopsia->DiagnosticoClinico }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Sitio de la Lesión:</span> 
                        {{ $biopsia->SitioLesion ?? 'N/A' }}
                    </div>
            @if($biopsia->DiagnosticoClinico)
           
            </div>
            @endif

            <!-- Descripción Macroscópica -->
            @if($biopsia->DescripciónMacroscópica)
            <div class="section">
                <div class="section-title">DESCRIPCIÓN MACROSCÓPICA</div>
                <div class="text-area-content">
                    {{ $biopsia->DescripciónMacroscópica }}
                </div>
            </div>
            @endif

            <!-- Descripción Microscópica -->
            @if($biopsia->DescripciónMicroscópica)
            <div class="section">
                <div class="section-title">DESCRIPCIÓN MICROSCÓPICA</div>
                <div class="text-area-content">
                    {{ $biopsia->DescripciónMicroscópica }}
                </div>
            </div>
            @endif

            <!-- Diagnóstico Microscópico -->
            <div class="section">
                <div class="section-title">DIAGNÓSTICO MICROSCÓPICO</div>
                <div class="diagnosis-box">
                    {{ $biopsia->DiagnósticoMicroscópico ?? 'Diagnóstico no disponible' }}
                </div>
            </div>

            <!-- Notas -->
            @if($biopsia->Notas)
            <div class="section">
                <div class="section-title">NOTAS</div>
                <div class="text-area-content">
                    {{ $biopsia->Notas }}
                </div>
            </div>
            @endif

            <!-- Comentarios Adicionales -->
            @if($biopsia->Com_1 || $biopsia->Com_2 || $biopsia->Com_3)
            <div class="section">
                <div class="section-title">COMENTARIOS ADICIONALES</div>
                <div class="text-area-content">
                    @if($biopsia->Com_1)
                        <strong>•</strong> {{ $biopsia->Com_1 }}<br>
                    @endif
                    @if($biopsia->Com_2)
                        <strong>•</strong> {{ $biopsia->Com_2 }}<br>
                    @endif
                    @if($biopsia->Com_3)
                        <strong>•</strong> {{ $biopsia->Com_3 }}
                    @endif
                </div>
            </div>
            @endif

            <!-- Firmas -->
            <div class="signature-area">
                <div class="signature-line">
                    {{ $biopsia->MedicoPatólogo1 ?? '____________________' }}
                    <br>
                    <small>Médico Patólogo 1</small>
                </div>
                <div class="signature-line">
                    {{ $biopsia->MedicoPatólogo2 ?? '____________________' }}
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
                {{$biopsia->id}}<hr>
                Se entrega informe original, quedando copia fiel y exacta en los archivos de este laboratorio, el cual no se hace responsable por posibles tachaduras, enmendaduras, copias o escaneos realizados.
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