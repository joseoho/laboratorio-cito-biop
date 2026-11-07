@extends('layouts.core')

@section('title', $case->codeLabel())

@section('content')
    @php($report = $case->report())
    @php($biopsy = $case->isBiopsy())

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <span class="badge-case">{{ $case->codeLabel() }}</span>
                <span class="badge {{ $biopsy ? 'text-bg-primary' : 'text-bg-success' }} ms-2">{{ $case->type->label() }}</span>
                <span class="badge text-bg-{{ $case->status->badge() }} ms-1">{{ $case->status->label() }}</span>
            </h4>
            <span class="muted small">{{ $case->patient?->display_name }} · Recibo {{ $case->patient?->id }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('cases.index') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
            @if (auth()->user()->canEditReports())
                <a href="{{ route('cases.header', $case) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-hashtag me-1"></i>Datos
                </a>
                <a href="{{ route('cases.edit', $case) }}" class="btn btn-sm btn-primary"><i class="fas fa-pen me-1"></i>Editar</a>
            @endif
            @if ($case->status === \App\Enums\CaseStatus::Delivered && auth()->user()->isAdmin())
                <form method="POST" action="{{ route('cases.reopen', $case) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-warning" onclick="return confirm('¿Reabrir el informe emitido para corregirlo?')">
                        <i class="fas fa-lock-open me-1"></i>Reabrir
                    </button>
                </form>
            @endif
            @if ($case->isPrintable())
                <a href="{{ route('cases.print', $case) }}" target="_blank" class="btn btn-sm btn-success">
                    <i class="fas fa-print me-1"></i>Imprimir
                </a>
            @else
                <span class="badge text-bg-light border">Complete el informe para imprimir</span>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body small">
                    <div class="section-title">Paciente</div>
                    <dl class="row mb-0">
                        <dt class="col-5">Nombre</dt><dd class="col-7">{{ $case->patient?->display_name }}</dd>
                        <dt class="col-5">Cédula</dt><dd class="col-7">{{ $case->patient?->document_number ?: '—' }}</dd>
                        <dt class="col-5">Sexo</dt><dd class="col-7">{{ $case->patient?->sex_label }}</dd>
                        <dt class="col-5">Edad</dt><dd class="col-7">{{ $case->patient?->age_label }}</dd>
                        <dt class="col-5">Teléfono</dt><dd class="col-7">{{ $case->patient?->phone ?: '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-body small">
                    <div class="section-title">Muestra</div>
                    <dl class="row mb-0">
                        <dt class="col-5">Recibido</dt><dd class="col-7">{{ $case->received_at?->format('d/m/Y') ?? '—' }}</dd>
                        <dt class="col-5">Enviado</dt><dd class="col-7">{{ $case->sent_at?->format('d/m/Y') ?? '—' }}</dd>
                        <dt class="col-5">Completado</dt><dd class="col-7">{{ $case->completed_at?->format('d/m/Y') ?? '—' }}</dd>
                        <dt class="col-5">Material</dt><dd class="col-7">{{ $report?->material ?: '—' }}</dd>
                        <dt class="col-5">Dx clínico</dt><dd class="col-7">{{ $report?->clinical_diagnosis ?: '—' }}</dd>
                        @if ($biopsy)
                            <dt class="col-5">Sitio</dt><dd class="col-7">{{ $report?->lesion_site ?: '—' }}</dd>
                        @endif
                    </dl>

                    @if ($case->flags)
                        <hr>
                        <div class="text-warning">{{ $case->flags }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="section-title">Informe</div>

                    @if ($biopsy)
                        @include('cases.partials.text-block', ['label' => 'Descripción macroscópica', 'text' => $report?->macro_description])
                        @include('cases.partials.text-block', ['label' => 'Descripción microscópica', 'text' => $report?->micro_description])
                        <div class="mb-3">
                            <div class="section-title">Diagnóstico microscópico</div>
                            <div class="p-3 bg-warning-subtle border border-warning rounded">
                                {{ $report?->micro_diagnosis ?: 'Sin diagnóstico registrado.' }}
                            </div>
                        </div>
                    @else
                        @include('cases.partials.text-block', ['label' => 'Descripción macroscópica', 'text' => $report?->macro_description])
                        @include('cases.partials.text-block', ['label' => 'Descripción citológica', 'text' => $report?->cyto_description])
                        @include('cases.partials.text-block', ['label' => 'Diagnóstico citológico', 'text' => $report?->cyto_diagnosis])
                        @include('cases.partials.text-block', ['label' => 'Calidad de la muestra', 'text' => $report?->quality])

                        @if ($report?->hasHormonalIndex())
                            <div class="row text-center mb-3">
                                <div class="col"><div class="section-title">Superficiales</div><strong>{{ $report->superficial ?? 0 }}</strong></div>
                                <div class="col"><div class="section-title">Intermedias</div><strong>{{ $report->intermediate ?? 0 }}</strong></div>
                                <div class="col"><div class="section-title">Parabasales</div><strong>{{ $report->parabasal ?? 0 }}</strong></div>
                            </div>
                            @include('cases.partials.text-block', ['label' => 'Conclusión', 'text' => $report?->conclusion])
                        @endif
                    @endif

                    @include('cases.partials.text-block', ['label' => 'Notas', 'text' => $report?->notes])
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="section-title">Patólogos asignados</div>
                    @forelse ($case->assignments as $assignment)
                        <div class="d-flex justify-content-between border-bottom py-1">
                            <span>{{ $assignment->pathologist_name }}</span>
                            <span class="muted small">{{ $assignment->role === 1 ? 'Titular' : 'Cosignante' }}</span>
                        </div>
                    @empty
                        <p class="muted mb-0">Sin patólogos asignados.</p>
                    @endforelse
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="section-title">Historial</div>
                    @forelse ($history as $event)
                        <div class="d-flex justify-content-between border-bottom py-1 small">
                            <span>
                                <strong>{{ $event->action }}</strong>
                                · {{ $event->actor?->name ?? 'sistema' }}
                                @if (! empty($event->changes['from']) || ! empty($event->changes['to']))
                                    <span class="muted">({{ $event->changes['from'] ?? '—' }} → {{ $event->changes['to'] ?? '—' }})</span>
                                @endif
                            </span>
                            <span class="muted">{{ $event->created_at?->format('d/m/Y H:i') }}</span>
                        </div>
                    @empty
                        <p class="muted mb-0">Sin eventos registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
