@extends('layouts.core')

@section('title', $patient->display_name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">{{ $patient->display_name }}</h4>
            <span class="muted small">Recibo {{ $patient->id }} · {{ $patient->cases->count() }} estudio(s)</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-pen me-1"></i>Editar
            </a>
            <a href="{{ route('cases.create', $patient) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Nuevo estudio
            </a>
            <a href="{{ route('patients.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="section-title">Identificación</div>
                    <dl class="row mb-0 small">
                        <dt class="col-5">Cédula</dt>
                        <dd class="col-7">
                            {{ $patient->document_number ?: '—' }}
                            @if ($patient->document_is_placeholder)
                                <span class="badge text-bg-warning ms-1">placeholder</span>
                            @endif
                        </dd>

                        <dt class="col-5">Sexo</dt><dd class="col-7">{{ $patient->sex_label }}</dd>
                        <dt class="col-5">Edad al ingreso</dt><dd class="col-7">{{ $patient->age_label }}</dd>
                        <dt class="col-5">Teléfono</dt>
                        <dd class="col-7">{{ trim(($patient->phone_code ?? '') . ' ' . ($patient->phone ?? '')) ?: '—' }}</dd>
                        <dt class="col-5">Dirección</dt><dd class="col-7">{{ $patient->address ?: '—' }}</dd>
                        <dt class="col-5">Ingreso</dt>
                        <dd class="col-7">{{ $patient->created_at?->format('d/m/Y') ?? '—' }}</dd>
                        <dt class="col-5">Médico</dt>
                        <dd class="col-7">{{ $patient->doctor?->name ?? '—' }}</dd>
                        <dt class="col-5">Procedencia</dt>
                        <dd class="col-7">{{ $patient->origin?->legacy_code ?? '—' }}</dd>
                        <dt class="col-5">Legajo Access</dt><dd class="col-7 text-muted">{{ $patient->legacy_id }}</dd>
                    </dl>

                    @if ($patient->flags)
                        <hr>
                        <div class="small">
                            <span class="section-title d-block">Marcas del legado</span>
                            <span class="text-warning">{{ $patient->flags }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="section-title">Estudios del paciente</div>
                    @if ($patient->cases->isEmpty())
                        <p class="muted mb-0">Este registro no tiene estudios asociados.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Estudio</th>
                                        <th>Tipo</th>
                                        <th>Recibido</th>
                                        <th>Estado</th>
                                        <th>Patólogos</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($patient->cases as $case)
                                    <tr>
                                        <td class="badge-case">{{ $case->codeLabel() }}</td>
                                        <td>{{ $case->type->label() }}</td>
                                        <td>{{ $case->received_at?->format('d/m/Y') ?? '—' }}</td>
                                        <td>
                                            <span class="badge text-bg-{{ $case->status->badge() }}">
                                                {{ $case->status->label() }}
                                            </span>
                                        </td>
                                        <td class="small">
                                            {{ $case->assignments->pluck('pathologist_name')->filter()->join(', ') ?: '—' }}
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('cases.show', $case) }}" class="btn btn-sm btn-outline-dark">Ver</a>
                                            <a href="{{ route('cases.edit', $case) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                            @if ($case->isPrintable())
                                                <a href="{{ route('cases.print', $case) }}" target="_blank" class="btn btn-sm btn-outline-success">Imprimir</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
