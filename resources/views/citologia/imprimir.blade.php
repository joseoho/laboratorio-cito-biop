<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Citología - {{ $citologia->paciente->NC ?? 'N/A' }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 0; font-family: 'Arial', sans-serif; font-size: 10px; color: #000; }
            .no-print { display: none !important; }
            .print-container { width: 21cm; height: 27.9cm; padding: 0.5cm; }
        }
        @media screen {
            body { font-family: 'Arial', sans-serif; font-size: 12px; margin: 10px; background-color: #f8f9fa; }
            .print-container { width: 21cm; min-height: 27.9cm; margin: 0 auto; background: white; padding: 0.5cm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        }
        .header-content { display: flex; align-items: center; gap: 15px; margin-bottom: 10px; }
        .logo { width: 80px; height: auto; }
        .lab-info { flex: 1; }
        .lab-name { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .lab-title { font-size: 12px; font-style: italic; margin-bottom: 3px; }
        .lab-details { font-size: 9px; line-height: 1.2; }
        .report-title { text-align: center; font-size: 14px; font-weight: bold; margin: 10px 0; text-decoration: underline; }
        .section { margin-bottom: 12px; }
        .section-title { font-weight: bold; background-color: #f0f0f0; padding: 3px 8px; margin-bottom: 6px; border-left: 3px solid #333; font-size: 11px; }
        .patient-info { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 10px; font-size: 10px; }
        .info-item { margin-bottom: 3px; }
        .info-label { font-weight: bold; }
        .text-area-content { border: 1px solid #ddd; padding: 6px; min-height: 40px; margin-bottom: 8px; white-space: pre-wrap; font-size: 10px; }
        .signature-area { margin-top: 20px; display: flex; justify-content: space-around; }
        .signature-line { border-top: 1px solid #000; width: 150px; text-align: center; padding-top: 3px; font-size: 10px; }
        .controls { text-align: center; margin-bottom: 10px; padding: 8px; background-color: #e9ecef; }
        .compact-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px; margin-bottom: 8px; font-size: 10px; }
        .footer { margin-top: 15px; padding-top: 8px; border-top: 1px solid #333; text-align: center; font-size: 8px; line-height: 1.1; }
        .btn { padding: 5px 10px; margin: 0 5px; cursor: pointer; }
        .hormonal-index { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 10px; }
        .index-item { text-align: center; }
        .index-value { font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="controls no-print">
        <button onclick="window.print()" class="btn">🖨️ Imprimir</button>
        <button onclick="window.close()" class="btn">❌ Cerrar</button>
        <button onclick="window.history.back()" class="btn">↩️ Volver</button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="header-content">
            <div>
                <img src="{{ asset('assets/images/logo.jpg') }}" class="logo" alt="Logo Laboratorio">
            </div>
            <div class="lab-info">
                <div class="lab-name">LABORATORIO DE ANATOMIA PATOLOGICA</div>
                <div class="lab-title">"Dr. Jose R. Becker Reyes"</div>
                <div class="lab-details">
                    <strong>Dirección:</strong> Urb. San Inés, Hotel Valle Santiango, CENTROMED, San Cristóbal, Edo Táchira<br>
                    <strong>Teléfono:</strong> 0276-3410380 • <strong>Email:</strong> becomarca@gmail.com
                </div>
            </div>
        </div>

        <!-- Título -->
        <div class="report-title">INFORME DE CITOLOGÍA</div>

        <!-- Información del Paciente -->
        <div class="section">
            <div class="section-title">INFORMACIÓN DEL PACIENTE</div>
            <div class="patient-info">
                <div class="info-item"><span class="info-label">No. Citología:</span> {{ $citologia->paciente->NC ?? 'N/A' }}</div>
                <div class="info-item"><span class="info-label">Cédula:</span> {{ $citologia->paciente->CI ?? 'N/A' }}</div>
                <div class="info-item"><span class="info-label">Recibo:</span> {{ $citologia->paciente->id ?? 'N/A' }}</div>
                <div class="info-item"><span class="info-label">Nombre:</span> {{ $citologia->paciente->Nombre ?? 'N/A' }}</div>
                <div class="info-item"><span class="info-label">Edad:</span> {{ $citologia->paciente->Edad ?? 'N/A' }}</div>
                <div class="info-item"><span class="info-label">Sexo:</span> {{ $citologia->paciente->Sexo ?? 'N/A' }}</div>
                <div class="info-item"><span class="info-label">Medico:</span> {{ $citologia->paciente->Dr ?? 'N/A' }}</div>
                <div class="info-item"><span class="info-label">Fecha Recepción:</span> @if($citologia->FechaRecibido) {{ \Carbon\Carbon::parse($citologia->FechaRecibido)->format('d/m/Y') }} @else N/A @endif</div>
                <div class="info-item"><span class="info-label">Fecha Envío:</span> @if($citologia->FechaEnvio) {{ \Carbon\Carbon::parse($citologia->FechaEnvio)->format('d/m/Y') }} @else N/A @endif</div>
            </div>
        </div>

        <!-- Información de la Muestra -->
        <div class="compact-grid">
            <div class="info-item"><span class="info-label">Material Remitido:</span> {{ $citologia->MaterialRemitido ?? 'N/A' }}</div>
            <div class="info-item"><span class="info-label">Diagnóstico Clínico:</span> {{ $citologia->DiagnosticoClinico ?? 'N/A' }}</div>
            <div class="info-item"><span class="info-label">Calidad de la Muestra:</span> {{ $citologia->Calidad ?? 'N/A' }}</div>
        </div>

        <!-- Descripción Macroscópica -->
        @if($citologia->DescripcionMacroscopica)
        <div class="section">
            <div class="section-title">DESCRIPCIÓN MACROSCÓPICA</div>
            <div class="text-area-content">{{ $citologia->DescripcionMacroscopica }}</div>
        </div>
        @endif

        <!-- Descripción Citológica -->
        @if($citologia->DescripcionCitologica)
        <div class="section">
            <div class="section-title">DESCRIPCIÓN CITOLÓGICA</div>
            <div class="text-area-content">{{ $citologia->DescripcionCitologica }}</div>
        </div>
        @endif

        <!-- Diagnóstico Citológico -->
        @if($citologia->DiagnosticoCitologico)
        <div class="section">
            <div class="section-title">DIAGNÓSTICO CITOLÓGICO</div>
            <div class="text-area-content">{{ $citologia->DiagnosticoCitologico }}</div>
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
            <div class="text-area-content"><strong>Conclusión:</strong> {{ $citologia->Conclusion }}</div>
            @endif
        </div>
        @endif

        <!-- Notas -->
        @if($citologia->Notas)
        <div class="section">
            <div class="section-title">NOTAS</div>
            <div class="text-area-content">{{ $citologia->Notas }}</div>
        </div>
        @endif

        <!-- Firmas -->
        <div class="signature-area">
            <div class="signature-line">
                {{ $citologia->MedicoPatologo1 ?? '____________________' }}<br>
                <small>Médico Patólogo 1</small>
            </div>
            <div class="signature-line">
                {{ $citologia->MedicoPatologo2 ?? '____________________' }}<br>
                <small>Médico Patólogo 2</small>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Urb. San Inés, Hotel Valle Santiango, CENTROMED, San Cristóbal, Edo Táchira • Tel: 0276-3410380 • Email: becomarca@gmail.com<br>
            Rif: J-29890247-7 • Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} • ID: {{$citologia->paciente->id}}<br>
            <hr style="margin: 5px 0;">
            Se entrega informe original, quedando copia fiel y exacta en los archivos de este laboratorio.
        </div>
    </div>

    <script>
        window.onafterprint = function() {
            // window.close();
        };
    </script>
</body>
</html>