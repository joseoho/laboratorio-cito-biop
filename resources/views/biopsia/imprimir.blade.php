<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Biopsia - {{ $biopsia->id ?? 'N/A' }}</title>
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
        .diagnosis-box { background-color: #fffacd; border: 1px solid #ff6b6b; padding: 8px; margin: 10px 0; font-weight: bold; font-size: 11px; }
        .signature-area { margin-top: 20px; display: flex; justify-content: space-around; }
        .signature-line { border-top: 1px solid #000; width: 150px; text-align: center; padding-top: 3px; font-size: 10px; }
        .controls { text-align: center; margin-bottom: 10px; padding: 8px; background-color: #e9ecef; }
        .compact-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px; margin-bottom: 8px; font-size: 10px; }
        .footer { margin-top: 15px; padding-top: 8px; border-top: 1px solid #333; text-align: center; font-size: 8px; line-height: 1.1; }
        .btn { padding: 5px 10px; margin: 0 5px; cursor: pointer; }
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
                <img src="{{ asset('assets/images/logo.png') }}" class="logo" alt="Logo Laboratorio">
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
        <div class="report-title">BIOPSIA</div>

        <!-- Información General -->
        <div class="section">
            <div class="section-title">INFORMACIÓN GENERAL</div>
            <div class="patient-info">
                <div class="info-item"><span class="info-label">No. Biopsia:</span> {{ $biopsia->paciente->NB ?? 'N/A' }}</div>
                <div class="info-item"><span class="info-label">Nombre:</span> {{ $biopsia->paciente->Nombre ?? 'N/A' }}</div>
                <div class="info-item"><span class="info-label">Procedencia:</span> {{ $biopsia->paciente->Procedencia ?? 'N/A' }}</div>
                <div class="info-item"><span class="info-label">Dr(a):</span> {{ $biopsia->paciente->Dr ?? 'N/A' }}</div>
                <div class="info-item"><span class="info-label">Recibida:</span> @if($biopsia->FechaRecibido) {{ \Carbon\Carbon::parse($biopsia->FechaRecibido)->format('d/m/Y') }} @else N/A @endif</div>
                <div class="info-item"><span class="info-label">Enviada:</span> @if($biopsia->FechaEnvio) {{ \Carbon\Carbon::parse($biopsia->FechaEnvio)->format('d/m/Y') }} @else N/A @endif</div>
            </div>
        </div>

        <!-- Información Clínica -->
        <div class="compact-grid">
            <div class="info-item"><span class="info-label">Material Remitido:</span> {{ $biopsia->MaterialRemitido ?? 'N/A' }}</div>
            <div class="info-item"><span class="info-label">Sitio Lesión:</span> {{ $biopsia->SitioLesion ?? 'N/A' }}</div>
            <div class="info-item"><span class="info-label">Diag. Clínico:</span> {{ $biopsia->DiagnosticoClinico ?? 'N/A' }}</div>
        </div>

        <!-- Descripción Macroscópica -->
        @if($biopsia->DescripciónMacroscópica)
        <div class="section">
            <div class="section-title">DESCRIPCIÓN MACROSCÓPICA</div>
            <div class="text-area-content">{{ $biopsia->DescripciónMacroscópica }}</div>
        </div>
        @endif

        <!-- Descripción Microscópica -->
        @if($biopsia->DescripciónMicroscópica)
        <div class="section">
            <div class="section-title">DESCRIPCIÓN MICROSCÓPICA</div>
            <div class="text-area-content">{{ $biopsia->DescripciónMicroscópica }}</div>
        </div>
        @endif

        <!-- Diagnóstico Microscópico -->
        <div class="section">
            <div class="section-title">DIAGNÓSTICO MICROSCÓPICO</div>
            <div class="diagnosis-box">{{ $biopsia->DiagnósticoMicroscópico ?? 'Diagnóstico no disponible' }}</div>
        </div>

        <!-- Notas -->
        @if($biopsia->Notas)
        <div class="section">
            <div class="section-title">NOTAS</div>
            <div class="text-area-content">{{ $biopsia->Notas }}</div>
        </div>
        @endif

        <!-- Comentarios Adicionales -->
        @if($biopsia->Com_1 || $biopsia->Com_2 || $biopsia->Com_3)
        <div class="section">
            <div class="section-title">COMENTARIOS ADICIONALES</div>
            <div class="text-area-content">
                @if($biopsia->Com_1)<strong>•</strong> {{ $biopsia->Com_1 }}<br>@endif
                @if($biopsia->Com_2)<strong>•</strong> {{ $biopsia->Com_2 }}<br>@endif
                @if($biopsia->Com_3)<strong>•</strong> {{ $biopsia->Com_3 }}@endif
            </div>
        </div>
        @endif

        <!-- Firmas -->
        <div class="signature-area">
            <div class="signature-line">
                {{ $biopsia->MedicoPatólogo1 ?? '____________________' }}<br>
                <small>Médico Patólogo 1</small>
            </div>
            <div class="signature-line">
                {{ $biopsia->MedicoPatólogo2 ?? '____________________' }}<br>
                <small>Médico Patólogo 2</small>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Urb. San Inés, Hotel Valle Santiango, CENTROMED, San Cristóbal, Edo Táchira • Tel: 0276-3410380 • Email: becomarca@gmail.com<br>
            Rif: J-29890247-7 • Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} • ID: {{$biopsia->id}}<br>
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