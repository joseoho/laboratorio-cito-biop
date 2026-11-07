<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <title>{{ $case->isBiopsy() ? 'Biopsia' : 'Informe de Citología' }} · {{ $case->codeLabel() }}</title>
    <style>
        @@page { size: letter; margin: 12mm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color:#000; margin:0; }
        .toolbar { background:#e9ecef; padding:8px; text-align:center; }
        .toolbar button { padding:6px 14px; margin:0 4px; cursor:pointer; }
        .sheet { width:18.6cm; margin:0 auto; padding:0.5cm; }
        .head { display:flex; gap:12px; align-items:center; border-bottom:2px solid #333; padding-bottom:6px; margin-bottom:8px; }
        .head img { width:70px; }
        .lab-name { font-size:14px; font-weight:bold; }
        .lab-sub { font-size:11px; font-style:italic; }
        .lab-meta { font-size:8.5px; line-height:1.3; }
        h1 { font-size:14px; text-align:center; text-decoration:underline; margin:8px 0; }
        .grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:4px 12px; font-size:10px; margin-bottom:8px; }
        .label { font-weight:bold; }
        .section { margin-bottom:9px; }
        .section-title { font-weight:bold; background:#eee; padding:2px 6px; border-left:3px solid #333; font-size:10px; margin-bottom:3px; }
        .text { border:1px solid #ddd; padding:5px; white-space:pre-wrap; font-size:10px; }
        .dx { background:#fff9c4; border:1px solid #c62828; padding:6px; font-weight:bold; font-size:11px; }
        .cuerpo { min-height:17cm; }
        .firmas { margin-top:12px; }
        .firmas td { border-bottom:1px solid #000; height:72px; vertical-align:bottom; padding-bottom:2px; }
        .firma-slot { height:42px; line-height:42px; }
        .firma { max-height:40px; max-width:150px; vertical-align:middle; }
        .firmante { font-size:10px; font-weight:bold; }
        .especialidad { font-size:7.5px; color:#333; }
        .foot { margin-top:14px; border-top:1px solid #333; padding-top:5px; font-size:8px; text-align:center; line-height:1.3; }
        .hormonal { display:grid; grid-template-columns:repeat(3,1fr); text-align:center; margin-bottom:6px; }
        @@media print { .toolbar { display:none; } body { margin:0; } .sheet { width:auto; padding:0; } }
    </style>
</head>
<body>
@php($report = $case->report())
@php($biopsy = $case->isBiopsy())
@php($signers = $case->assignments->pluck('pathologist_name')->filter())
@php($firma = $case->signed ? \App\Support\Signature::dataUri() : null)

<div class="toolbar">
    <button onclick="window.print()">Imprimir</button>
    <button onclick="window.close()">Cerrar</button>
</div>

<div class="sheet">
    <div class="cuerpo">
    <div class="head">
        <img src="{{ asset('assets/images/logo.jpg') }}" alt="Logo">
        <div>
            <div class="lab-name">LABORATORIO BECOMARCA</div>
            <div class="lab-sub">Anatomía Patológica · "Dr. Juan Carlos Becker"</div>
            <div class="lab-meta">
                Urb. San Inés, Hotel Valle Santiango, CENTROMED, San Cristóbal, Edo. Táchira<br>
                Teléfono: 0276-3410380 · Email: becomarca@gmail.com · RIF: J-29890247-7
            </div>
        </div>
    </div>

    <h1>{{ $biopsy ? 'BIOPSIA' : 'INFORME DE CITOLOGÍA' }}</h1>

    <div class="grid">
        <div><span class="label">{{ $biopsy ? 'No. Biopsia:' : 'No. Citología:' }}</span> {{ $case->codeLabel() }}</div>
        <div><span class="label">Cédula:</span> {{ $case->patient?->document_number ?: 'N/A' }}</div>
        <div><span class="label">Recibo:</span> {{ $case->patient?->id }}</div>

        <div><span class="label">Nombre:</span> {{ $case->patient?->display_name }}</div>
        <div><span class="label">Edad:</span> {{ $case->patient?->age_label }}</div>
        <div><span class="label">Sexo:</span> {{ $case->patient?->sex_label }}</div>

        <div><span class="label">Recibido:</span> {{ $case->received_at?->format('d/m/Y') ?? 'N/A' }}</div>
        <div><span class="label">Enviado:</span> {{ $case->sent_at?->format('d/m/Y') ?? 'N/A' }}</div>
        <div><span class="label">Material:</span> {{ $report?->material ?: 'N/A' }}</div>
    </div>

    <div class="section">
        <div class="section-title">DIAGNÓSTICO CLÍNICO</div>
        <div class="text">{{ $report?->clinical_diagnosis ?: 'N/A' }}</div>
    </div>

    @if ($biopsy)
        <div class="section">
            <div class="section-title">SITIO DE LA LESIÓN</div>
            <div class="text">{{ $report?->lesion_site ?: 'N/A' }}</div>
        </div>

        @if (filled($report?->macro_description))
            <div class="section">
                <div class="section-title">DESCRIPCIÓN MACROSCÓPICA</div>
                <div class="text">{{ $report->macro_description }}</div>
            </div>
        @endif

        @if (filled($report?->micro_description))
            <div class="section">
                <div class="section-title">DESCRIPCIÓN MICROSCÓPICA</div>
                <div class="text">{{ $report->micro_description }}</div>
            </div>
        @endif

        <div class="section">
            <div class="section-title">DIAGNÓSTICO MICROSCÓPICO</div>
            <div class="dx">{{ $report?->micro_diagnosis ?: 'Diagnóstico no disponible' }}</div>
        </div>
    @else
        @if (filled($report?->macro_description))
            <div class="section">
                <div class="section-title">DESCRIPCIÓN MACROSCÓPICA</div>
                <div class="text">{{ $report->macro_description }}</div>
            </div>
        @endif

        @if (filled($report?->cyto_description))
            <div class="section">
                <div class="section-title">DESCRIPCIÓN CITOLÓGICA</div>
                <div class="text">{{ $report->cyto_description }}</div>
            </div>
        @endif

        @if (filled($report?->cyto_diagnosis))
            <div class="section">
                <div class="section-title">DIAGNÓSTICO CITOLÓGICO</div>
                <div class="dx">{{ $report->cyto_diagnosis }}</div>
            </div>
        @endif

        @if (filled($report?->quality))
            <div class="section">
                <div class="section-title">CALIDAD DE LA MUESTRA</div>
                <div class="text">{{ $report->quality }}</div>
            </div>
        @endif

        @if ($report?->hasHormonalIndex())
            <div class="section">
                <div class="section-title">ÍNDICE DE VALORACIÓN HORMONAL</div>
                <div class="hormonal">
                    <div><span class="label">Superficiales</span><br>{{ $report->superficial ?? 0 }}</div>
                    <div><span class="label">Intermedias</span><br>{{ $report->intermediate ?? 0 }}</div>
                    <div><span class="label">Parabasales</span><br>{{ $report->parabasal ?? 0 }}</div>
                </div>
                @if (filled($report->conclusion))
                    <div class="text"><span class="label">Conclusión:</span> {{ $report->conclusion }}</div>
                @endif
            </div>
        @endif
    @endif

    @if (filled($report?->notes))
        <div class="section">
            <div class="section-title">NOTAS</div>
            <div class="text">{{ $report->notes }}</div>
        </div>
    @endif
    </div>{{-- /cuerpo --}}

    <table class="firmas" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="45%" align="center">
                <div class="firma-slot"></div>
                <div class="firmante">{{ $signers->get(1) ?: '' }}</div>
                <div class="especialidad">{{ $signers->get(1) ? 'Anatomía Patológica' : '' }}</div>
            </td>
            <td width="10%"></td>
            <td width="45%" align="center">
                <div class="firma-slot"><!-- firma-v5 -->
                    @if ($firma)
                        <img class="firma" src="{{ $firma }}" alt="Firma digitalizada">
                    @endif
                </div>
                <div class="firmante">{{ $signers->get(0) ?: '' }}</div>
                <div class="especialidad">{{ $signers->get(0) ? 'Anatomía Patológica' : '' }}</div>
            </td>
        </tr>
    </table>

    <div class="foot">
        Urb. San Inés, Hotel Valle Santiango, CENTROMED, San Cristóbal, Edo. Táchira ·
        Tel: 0276-3410380 · Email: becomarca@gmail.com<br>
        RIF: J-29890247-7 · Fecha: {{ now()->format('d/m/Y H:i') }} · Estudio: {{ $case->codeLabel() }}<br>
        Se entrega informe original, quedando copia fiel y exacta en los archivos de este laboratorio.
    </div>
</div>
</body>
</html>
