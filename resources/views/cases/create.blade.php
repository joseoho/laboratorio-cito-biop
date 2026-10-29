@extends('layouts.core')

@section('title', 'Nuevo estudio')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Nuevo estudio</h4>
            <span class="muted small">{{ $patient->display_name }} · Recibo {{ $patient->id }}</span>
        </div>
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-secondary">Volver</a>
    </div>

    <form method="POST" action="{{ route('cases.store', $patient) }}">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Corrige lo siguiente:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <div class="section-title">Tipo y número</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tipo de estudio</label>
                        <select name="type" id="caseType" class="form-select">
                            <option value="biopsy" @selected(old('type', 'biopsy') === 'biopsy')>Biopsia</option>
                            <option value="cytology" @selected(old('type') === 'cytology')>Citología</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Número</label>
                        <input type="text" name="case_code" id="caseCode"
                               class="form-control @error('case_code') is-invalid @enderror"
                               value="{{ old('case_code', $nextBiopsy) }}" inputmode="numeric" autocomplete="off">
                        @error('case_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text" id="codeHint"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha de recepción</label>
                        <input type="date" name="received_at" class="form-control @error('received_at') is-invalid @enderror"
                               value="{{ old('received_at', $today) }}">
                        @error('received_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="section-title">Muestra</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Material remitido</label>
                        <input type="text" name="material" class="form-control" value="{{ old('material') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Diagnóstico clínico</label>
                        <input type="text" name="clinical_diagnosis" class="form-control" value="{{ old('clinical_diagnosis') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <span class="muted small">
                El número se propone automáticamente (secuencia + año). Puede corregirlo si el laboratorio ya lo asignó.
            </span>
            <div class="d-flex gap-2">
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Crear estudio
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var next = {
        biopsy: @json($nextBiopsy),
        cytology: @json($nextCytology)
    };
    var hints = {
        biopsy: '6 dígitos: secuencia de 4 + año de 2 (ej. ' + next.biopsy + ').',
        cytology: '7 dígitos: secuencia de 5 + año de 2 (ej. ' + next.cytology + ').'
    };

    var type = document.getElementById('caseType');
    var code = document.getElementById('caseCode');
    var hint = document.getElementById('codeHint');

    function apply(force) {
        var selected = type.value;
        if (force || !code.value || code.value === next.biopsy || code.value === next.cytology) {
            code.value = next[selected];
        }
        hint.textContent = hints[selected];
    }

    type.addEventListener('change', function () { apply(true); });
    apply(false);
})();
</script>
@endpush
