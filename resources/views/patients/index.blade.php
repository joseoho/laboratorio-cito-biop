@extends('layouts.core')

@section('title', 'Pacientes')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h4 class="mb-0">Pacientes</h4>
            <span class="muted small">{{ number_format($patients->total(), 0, ',', '.') }} registros históricos</span>
        </div>
        <div class="d-flex gap-2">
            @if ($term !== '')
                <a href="{{ route('patients.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Limpiar búsqueda
                </a>
            @endif
            <a href="{{ route('patients.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Nuevo paciente
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('patients.index') }}" class="search-box mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="search" value="{{ $term }}" class="form-control"
                       placeholder="Cédula, nombre, número de biopsia o citología...">
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button class="btn btn-outline-secondary" name="tipo" value="ci">
                    <i class="fas fa-id-card me-1"></i>CI / Nombre
                </button>
                <button class="btn btn-outline-primary" name="tipo" value="nb">
                    <i class="fas fa-flask me-1"></i>NB
                </button>
                <button class="btn btn-outline-success" name="tipo" value="nc">
                    <i class="fas fa-vial me-1"></i>NC
                </button>
                <button class="btn btn-primary ms-auto" type="submit">
                    <i class="fas fa-search me-1"></i>Buscar
                </button>
            </div>
        </div>
        @if ($term !== '')
            <div class="small muted mt-2">
                Buscando por
                <strong>{{ $type === 'nb' ? 'Número de biopsia' : ($type === 'nc' ? 'Número de citología' : 'cédula / nombre') }}</strong>:
                "{{ $term }}"
            </div>
        @endif
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Recibo</th>
                        <th style="width:100px">Ingreso</th>
                        <th style="width:130px">Cédula</th>
                        <th>Nombre</th>
                        <th style="width:70px">Sexo</th>
                        <th style="width:80px">Edad</th>
                        <th style="width:230px">Estudios</th>
                        <th style="width:90px"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($patients as $patient)
                    <tr>
                        <td class="text-muted">{{ $patient->id }}</td>
                        <td class="small">{{ $patient->created_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $patient->document_number ?: '—' }}</td>
                        <td>
                            {{ $patient->display_name }}
                            @if ($patient->flags)
                                <div class="flag text-warning">{{ $patient->flags }}</div>
                            @endif
                        </td>
                        <td>{{ $patient->sex ?? '—' }}</td>
                        <td>{{ $patient->age_label }}</td>
                        <td>
                            @forelse ($patient->cases->sortByDesc('id')->take(3) as $case)
                                <a href="{{ route('cases.show', $case) }}"
                                   class="badge badge-case text-decoration-none {{ $case->isBiopsy() ? 'text-bg-primary' : 'text-bg-success' }}">
                                    {{ $case->codeLabel() }}
                                </a>
                            @empty
                                <span class="muted small">sin estudio</span>
                            @endforelse
                            @if ($patient->cases_count > 3)
                                <span class="muted small">+{{ $patient->cases_count - 3 }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-dark">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 muted">
                            No se encontraron pacientes con ese criterio.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $patients->links('pagination::bootstrap-5') }}
    </div>
@endsection
