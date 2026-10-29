@extends('layouts.core')

@section('title', 'Estudios')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h4 class="mb-0">Estudios</h4>
            <span class="muted small">{{ number_format($cases->total(), 0, ',', '.') }} resultados</span>
        </div>
        @if (request()->anyFilled(['search', 'tipo', 'estado', 'anio']))
            <a href="{{ route('cases.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar filtros</a>
        @endif
    </div>

    <form method="GET" action="{{ route('cases.index') }}" class="search-box mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="Número de estudio, paciente o cédula...">
            </div>
            <div class="col-md-2">
                <select name="tipo" class="form-select">
                    <option value="">Todos los tipos</option>
                    <option value="biopsy" @selected(request('tipo') === 'biopsy')>Biopsia</option>
                    <option value="cytology" @selected(request('tipo') === 'cytology')>Citología</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="received" @selected(request('estado') === 'received')>Recibido</option>
                    <option value="in_progress" @selected(request('estado') === 'in_progress')>En proceso</option>
                    <option value="delivered" @selected(request('estado') === 'delivered')>Entregado</option>
                    <option value="cancelled" @selected(request('estado') === 'cancelled')>Anulado</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="anio" value="{{ request('anio') }}" class="form-control"
                       placeholder="Año" min="2000" max="2100">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filtrar</button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:120px">Estudio</th>
                        <th style="width:100px">Tipo</th>
                        <th>Paciente</th>
                        <th style="width:120px">Cédula</th>
                        <th style="width:110px">Recibido</th>
                        <th style="width:120px">Estado</th>
                        <th>Patólogos</th>
                        <th style="width:190px"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($cases as $case)
                    <tr>
                        <td class="badge-case">{{ $case->codeLabel() }}</td>
                        <td>
                            <span class="badge {{ $case->isBiopsy() ? 'text-bg-primary' : 'text-bg-success' }}">
                                {{ $case->type->label() }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('patients.show', $case->patient) }}" class="text-decoration-none">
                                {{ $case->patient?->display_name ?? '—' }}
                            </a>
                        </td>
                        <td>{{ $case->patient?->document_number ?: '—' }}</td>
                        <td>{{ $case->received_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $case->status->badge() }}">{{ $case->status->label() }}</span>
                        </td>
                        <td class="small">{{ $case->assignments->pluck('pathologist_name')->filter()->join(', ') ?: '—' }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('cases.show', $case) }}" class="btn btn-sm btn-outline-dark">Ver</a>
                            <a href="{{ route('cases.edit', $case) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <a href="{{ route('cases.print', $case) }}" target="_blank" class="btn btn-sm btn-outline-success">Imprimir</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 muted">Sin resultados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $cases->links('pagination::bootstrap-5') }}</div>
@endsection
