@php
    $report = $case->report();
    $biopsy = $case->isBiopsy();
    $signers = $case->assignments->pluck('pathologist_name')->filter();
    $logoPath = public_path('assets/images/logo.jpg');
    $logo = file_exists($logoPath)
        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
        : null;
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #000; }
        .head td { vertical-align: middle; }
        .lab-name { font-size: 14px; font-weight: bold; }
        .lab-sub { font-size: 11px; font-style: italic; }
        .lab-meta { font-size: 8px; line-height: 1.35; }
        .rule { border-top: 2px solid #333; margin: 6px 0 8px; }
        .title { text-align: center; font-size: 13px; font-weight: bold; text-decoration: underline; margin: 10px 0; }
        .info td { padding: 2px 4px; font-size: 9.5px; }
        .label { font-weight: bold; }
        .sec-title { background: #eeeeee; border-left: 3px solid #333333; padding: 2px 6px; font-weight: bold; font-size: 9.5px; margin: 8px 0 3px; }
        .text { border: 1px solid #dddddd; padding: 5px; white-space: pre-wrap; font-size: 9.5px; }
        .dx { background: #fff9c4; border: 1px solid #c62828; padding: 6px; font-weight: bold; font-size: 10px; }
        .sign td { border-top: 1px solid #000; text-align: center; font-size: 9px; padding-top: 3px; }
        .foot { border-top: 1px solid #333; margin-top: 14px; padding-top: 5px; text-align: center; font-size: 7.5px; line-height: 1.35; }
        .muted { color: #555555; }
    </style>
</head>
<body>
    <table class="head" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            @if ($logo)
                <td width="80"><img src="{{ $logo }}" width="68" alt="Logo"></td>
            @endif
            <td>
                <div class="lab-name">LABORATORIO BECOMARCA</div>
                <div class="lab-sub">Anatomía Patológica · "Dr. Juan Carlos Becker"</div>
                <div class="lab-meta">
                    Urb. San Inés, Hotel Valle Santiango, CENTROMED, San Cristóbal, Edo. Táchira<br>
                    Teléfono: 0276-3410380 · Email: becomarca@gmail.com · RIF: J-29890247-7
                </div>
            </td>
        </tr>
    </table>
    <div class="rule"></div>

    <div class="title">{{ $biopsy ? 'BIOPSIA' : 'INFORME DE CITOLOGÍA' }}</div>

    <table class="info" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="33%"><span class="label">{{ $biopsy ? 'No. Biopsia:' : 'No. Citología:' }}</span> {{ $case->codeLabel() }}</td>
            <td width="33%"><span class="label">Cédula:</span> {{ $case->patient?->document_number ?: 'N/A' }}</td>
            <td width="34%"><span class="label">Recibo:</span> {{ $case->patient?->id }}</td>
        </tr>
        <tr>
            <td><span class="label">Nombre:</span> {{ $case->patient?->display_name }}</td>
            <td><span class="label">Edad:</span> {{ $case->patient?->age_label }}</td>
            <td><span class="label">Sexo:</span> {{ $case->patient?->sex_label }}</td>
        </tr>
        <tr>
            <td><span class="label">Recibido:</span> {{ $case->received_at?->format('d/m/Y') ?? 'N/A' }}</td>
            <td><span class="label">Enviado:</span> {{ $case->sent_at?->format('d/m/Y') ?? 'N/A' }}</td>
            <td><span class="label">Material:</span> {{ $report?->material ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">Diagnóstico clínico:</span> {{ $report?->clinical_diagnosis ?: 'N/A' }}</td>
            <td>
                @if ($biopsy)
                    <span class="label">Sitio:</span> {{ $report?->lesion_site ?: 'N/A' }}
                @endif
            </td>
        </tr>
    </table>

    @if (filled($report?->macro_description))
        <div class="sec-title">DESCRIPCIÓN MACROSCÓPICA</div>
        <div class="text">{{ $report->macro_description }}</div>
    @endif

    @if ($biopsy)
        @if (filled($report?->micro_description))
            <div class="sec-title">DESCRIPCIÓN MICROSCÓPICA</div>
            <div class="text">{{ $report->micro_description }}</div>
        @endif

        <div class="sec-title">DIAGNÓSTICO MICROSCÓPICO</div>
        <div class="dx">{{ $report?->micro_diagnosis ?: 'Diagnóstico no disponible' }}</div>
    @else
        @if (filled($report?->cyto_description))
            <div class="sec-title">DESCRIPCIÓN CITOLÓGICA</div>
            <div class="text">{{ $report->cyto_description }}</div>
        @endif

        @if (filled($report?->cyto_diagnosis))
            <div class="sec-title">DIAGNÓSTICO CITOLÓGICO</div>
            <div class="dx">{{ $report->cyto_diagnosis }}</div>
        @endif

        @if (filled($report?->quality))
            <div class="sec-title">CALIDAD DE LA MUESTRA</div>
            <div class="text">{{ $report->quality }}</div>
        @endif

        @if ($report?->hasHormonalIndex())
            <div class="sec-title">ÍNDICE DE VALORACIÓN HORMONAL</div>
            <table width="100%" cellspacing="0" cellpadding="3">
                <tr>
                    <td align="center"><span class="label">Superficiales</span><br>{{ $report->superficial ?? 0 }}</td>
                    <td align="center"><span class="label">Intermedias</span><br>{{ $report->intermediate ?? 0 }}</td>
                    <td align="center"><span class="label">Parabasales</span><br>{{ $report->parabasal ?? 0 }}</td>
                </tr>
            </table>
            @if (filled($report->conclusion))
                <div class="text"><span class="label">Conclusión:</span> {{ $report->conclusion }}</div>
            @endif
        @endif
    @endif

    @if (filled($report?->notes))
        <div class="sec-title">NOTAS</div>
        <div class="text">{{ $report->notes }}</div>
    @endif

    <table class="sign" width="100%" cellspacing="0" cellpadding="0" style="margin-top: 28px;">
        <tr>
            <td width="45%">{{ $signers->get(0) ?: '____________________' }}<br><span class="muted">Médico Patólogo 1</span></td>
            <td width="10%"></td>
            <td width="45%">{{ $signers->get(1) ?: '____________________' }}<br><span class="muted">Médico Patólogo 2</span></td>
        </tr>
    </table>

    <div class="foot">
        Urb. San Inés, Hotel Valle Santiango, CENTROMED, San Cristóbal, Edo. Táchira ·
        Tel: 0276-3410380 · Email: becomarca@gmail.com<br>
        RIF: J-29890247-7 · Fecha de emisión: {{ $case->completed_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }} · Estudio: {{ $case->codeLabel() }}<br>
        Se entrega informe original, quedando copia fiel y exacta en los archivos de este laboratorio.
    </div>
</body>
</html>
