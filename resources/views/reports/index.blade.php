@extends('layouts.core')

@section('title', 'Reportes')

@section('content')
    @php
        $total = (int) ($totals->total ?? 0);
        $delivered = (int) ($totals->delivered ?? 0);
        $inProgress = (int) ($totals->in_progress ?? 0);
        $pctDelivered = $total > 0 ? round($delivered * 100 / $total, 1) : 0;
        $months = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
    @endphp

    <style>
        .bar-track { background:#e9ecef; height:12px; border-radius:6px; overflow:hidden; }
        .bar-fill { background:#0d6efd; height:12px; }
        .bar-fill.ok { background:#198754; }
        @@media print {
            .no-print { display:none !important; }
            body { background:#fff; }
            .card { box-shadow:none; border:1px solid #ccc; }
        }
    </style>

    <div class="card mb-3">
        <div class="card-body d-flex align-items-center gap-3">
            <img src="{{ asset('assets/images/logo.jpg') }}" alt="Becomarca" style="height:78px;width:auto">
            <div>
                <div class="fw-bold" style="font-size:1.15rem">LABORATORIO BECOMARCA</div>
                <div class="small">Anatomía Patológica · Dr. José R. Becker Reyes</div>
                <div class="small muted">Urb. San Inés, Hotel Valle Santiango, CENTROMED, San Cristóbal, Edo. Táchira</div>
                <div class="small muted">Tel. 0276-3410380 · becomarca@gmail.com · RIF J-29890247-7</div>
            </div>
            <div class="ms-auto text-end">
                <div class="section-title mb-1">Reporte de producción</div>
                <div class="fs-4 fw-bold">{{ $year }}</div>
                <div class="small muted">
                    {{ $type === 'biopsy' ? 'Biopsias' : ($type === 'cytology' ? 'Citologías' : 'Todos los estudios') }}
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h4 class="mb-0">Reportes</h4>
            <span class="muted small">Producción y pendientes del laboratorio</span>
        </div>
        <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
            <i class="fas fa-print me-1"></i>Imprimir
        </button>
    </div>

    <form method="GET" action="{{ route('reports.index') }}" class="search-box mb-3 no-print">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Año</label>
                <select name="anio" class="form-select">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" @selected($y === $year)>{{ $y }}@if (! in_array($y, $yearsWithData, true)) (sin datos)@endif</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="biopsy" @selected($type === 'biopsy')>Biopsias</option>
                    <option value="cytology" @selected($type === 'cytology')>Citologías</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Aplicar</button>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="section-title">Estudios del período</div>
                <div class="fs-3 fw-bold">{{ number_format($total, 0, ',', '.') }}</div>
                <div class="small muted">
                    {{ number_format((int) $totals->biopsies, 0, ',', '.') }} biopsias ·
                    {{ number_format((int) $totals->cytologies, 0, ',', '.') }} citologías
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="section-title">Emitidos</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($delivered, 0, ',', '.') }}</div>
                <div class="small muted">{{ $pctDelivered }}% del período</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="section-title">En proceso</div>
                <div class="fs-3 fw-bold text-warning">{{ number_format($inProgress, 0, ',', '.') }}</div>
                <div class="small muted">sin informe emitido</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="section-title">Sin patólogo titular</div>
                <div class="fs-3 fw-bold text-secondary">{{ number_format($withoutPathologist, 0, ',', '.') }}</div>
                <div class="small muted">
                    {{ $total > 0 ? round($withoutPathologist * 100 / $total, 1) : 0 }}% del período
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="section-title">Producción por mes · {{ $year }}</div>
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px">Mes</th>
                                <th style="width:70px" class="text-end">Total</th>
                                <th style="width:80px" class="text-end">Emitidos</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($months as $m => $label)
                            @php($count = $monthly[$m] ?? 0)
                            @php($done = $monthlyDelivered[$m] ?? 0)
                            <tr>
                                <td>{{ $label }}</td>
                                <td class="text-end">{{ number_format($count, 0, ',', '.') }}</td>
                                <td class="text-end text-success">{{ number_format($done, 0, ',', '.') }}</td>
                                <td>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: {{ round($count * 100 / $maxMonthly) }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td class="text-end">{{ number_format($total, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($delivered, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="section-title">Pendientes por antigüedad</div>
                    <div class="row text-center">
                        <div class="col">
                            <div class="fs-4 fw-bold text-success">{{ number_format((int) $pending->up_to_7, 0, ',', '.') }}</div>
                            <div class="small muted">hasta 7 días</div>
                        </div>
                        <div class="col">
                            <div class="fs-4 fw-bold text-warning">{{ number_format((int) $pending->up_to_30, 0, ',', '.') }}</div>
                            <div class="small muted">8 a 30 días</div>
                        </div>
                        <div class="col">
                            <div class="fs-4 fw-bold text-danger">{{ number_format((int) $pending->over_30, 0, ',', '.') }}</div>
                            <div class="small muted">más de 30 días</div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between small">
                        <span class="muted">Total pendientes</span>
                        <strong>{{ number_format((int) $pending->total, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title">Procedencia</div>
                    <table class="table table-sm mb-0">
                        <tbody>
                        @forelse ($origins as $row)
                            <tr>
                                <td>{{ $row->label }}</td>
                                <td class="text-end">{{ number_format($row->cases, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td class="muted">Sin datos.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <div class="section-title">Productividad por patólogo</div>
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Patólogo</th>
                                <th class="text-end">Casos</th>
                                <th class="text-end">Emitidos</th>
                                <th style="width:30%">Avance</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($pathologists as $row)
                            @php($pct = $row->cases > 0 ? round($row->delivered * 100 / $row->cases) : 0)
                            <tr>
                                <td>{{ $row->name ?: '(sin nombre)' }}</td>
                                <td class="text-end">{{ number_format($row->cases, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($row->delivered, 0, ',', '.') }}</td>
                                <td>
                                    <div class="bar-track">
                                        <div class="bar-fill ok" style="width: {{ $pct }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="muted">Sin asignaciones en el período.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <div class="section-title">Médicos que remiten</div>
                    <table class="table table-sm mb-0">
                        <tbody>
                        @forelse ($doctors as $row)
                            <tr>
                                <td>{{ $row->label }}</td>
                                <td class="text-end">{{ number_format($row->cases, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td class="muted">Sin datos.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="footer mt-4 pt-3 border-top text-center small text-muted">
        Urb. San Inés, Hotel Valle Santiango, CENTROMED, San Cristóbal, Edo. Táchira ·
        Tel: 0276-3410380 · Email: becomarca@gmail.com<br>
        RIF: J-29890247-7 · Fecha: {{ now()->format('d/m/Y H:i') }} · Reporte de producción {{ $year }}<br>
        Documento interno del laboratorio, generado por el sistema.
    </div>
@endsection
