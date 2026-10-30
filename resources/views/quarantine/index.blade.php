@extends('layouts.core')

@section('title', 'Cuarentena')

@section('content')
    @php($pendingTotal = collect($counts)->sum('pending'))

    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h4 class="mb-0">Cuarentena de datos</h4>
            <span class="muted small">
                Anomalías detectadas en la migración. Cada una requiere una decisión del laboratorio.
            </span>
        </div>
        <span class="badge text-bg-warning fs-6">{{ number_format($pendingTotal, 0, ',', '.') }} pendientes</span>
    </div>

    <div class="mb-3 d-flex flex-wrap gap-2">
        @foreach ($counts as $name => $c)
            <a href="{{ route('quarantine.index', ['bucket' => $name, 'estado' => $status]) }}"
               class="btn btn-sm {{ $bucket === $name ? 'btn-dark' : 'btn-outline-secondary' }}">
                {{ $name }}
                <span class="badge {{ $c['pending'] > 0 ? 'text-bg-warning' : 'text-bg-success' }} ms-1">
                    {{ number_format($c['pending'], 0, ',', '.') }}
                </span>
                <span class="text-muted small">/ {{ number_format($c['total'], 0, ',', '.') }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('quarantine.index') }}" class="search-box mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="pending" @selected($status === 'pending')>Pendientes</option>
                    <option value="reviewed" @selected($status === 'reviewed')>Revisados</option>
                    <option value="ignored" @selected($status === 'ignored')>Ignorados</option>
                    <option value="all" @selected($status === 'all')>Todos</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Grupo</label>
                <select name="bucket" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($counts as $name => $c)
                        <option value="{{ $name }}" @selected($bucket === $name)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filtrar</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('quarantine.index') }}" class="btn btn-outline-secondary w-100">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:180px">Grupo</th>
                        <th>Detalle</th>
                        <th style="width:110px">Clave</th>
                        <th style="width:110px">Paciente</th>
                        <th style="width:120px">Estado</th>
                        <th style="width:280px">Decisión</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($items as $item)
                    @php($patientId = $patientMap[(int) $item->legacy_key] ?? null)
                    <tr>
                        <td><span class="badge text-bg-light border">{{ $item->bucket }}</span></td>
                        <td class="small">
                            {{ $item->detail }}
                            @if ($item->note)
                                <div class="text-muted">Nota: {{ $item->note }}</div>
                            @endif
                            @if ($item->reviewed_at)
                                <div class="text-muted">
                                    {{ $item->status === 'reviewed' ? 'Revisado' : 'Ignorado' }}
                                    el {{ $item->reviewed_at?->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $item->legacy_key }}</td>
                        <td class="small">
                            @if ($patientId)
                                <a href="{{ route('patients.show', $patientId) }}" class="text-decoration-none">Ver ficha</a>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td>
                            @php($badge = ['pending' => 'warning', 'reviewed' => 'success', 'ignored' => 'secondary'][$item->status] ?? 'light')
                            <span class="badge text-bg-{{ $badge }}">{{ $item->status }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('quarantine.resolve', $item) }}" class="d-flex gap-1">
                                @csrf
                                <select name="status" class="form-select form-select-sm" style="width:110px">
                                    <option value="reviewed" @selected($item->status === 'reviewed')>Revisado</option>
                                    <option value="ignored" @selected($item->status === 'ignored')>Ignorar</option>
                                    <option value="pending" @selected($item->status === 'pending')>Pendiente</option>
                                </select>
                                <input type="text" name="note" class="form-control form-control-sm" placeholder="Nota"
                                       value="{{ $item->note }}" maxlength="500">
                                <button class="btn btn-sm btn-outline-primary">Guardar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 muted">
                            No hay casos con ese filtro.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $items->links('pagination::bootstrap-5') }}</div>
@endsection
