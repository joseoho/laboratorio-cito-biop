@extends('layouts.core')

@section('title', 'Editar ' . $case->codeLabel())

@php($report = $case->report())
@php($biopsy = $case->isBiopsy())
@php($missing = $case->missingForEmission())
@php($macroTemplates = $templates->get(\App\Models\Template::MODULE_MACRO, collect()))
@php($microTemplates = $templates->get(\App\Models\Template::MODULE_MICRO, collect()))
@php($dxTemplates = $templates->get(\App\Models\Template::MODULE_DIAGNOSIS, collect()))

@section('content')
    <form method="POST" action="{{ route('cases.update', $case) }}" id="reportForm">
        @csrf
        @method('PUT')

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
                @if ($case->isPrintable())
                    <a href="{{ route('cases.print', $case) }}" target="_blank" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </a>
                @endif
                <a href="{{ route('cases.show', $case) }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-sm btn-primary" id="btnSave">
                    <i class="fas fa-save me-1"></i>Guardar informe
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Revisa los siguientes puntos:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($case->status === \App\Enums\CaseStatus::Delivered)
            <div class="alert alert-success">
                <i class="fas fa-check me-1"></i>
                Informe <strong>Entregado</strong>: ya se puede imprimir.
            </div>
        @elseif (count($missing) > 0)
            <div class="alert alert-warning">
                <i class="fas fa-lock me-1"></i>
                Al guardar con todos los campos, el estudio pasa a <strong>Entregado</strong> y se habilita la impresión.
                Falta: <strong>{{ implode(', ', $missing) }}</strong>.
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i>
                El informe está completo: al guardar quedará <strong>Entregado</strong> y listo para imprimir.
            </div>
        @endif

        <div class="row g-3">
            {{-- Columna izquierda: contexto clínico, siempre visible --}}
            <div class="col-lg-4">
                <div class="card sticky-panel">
                    <div class="card-body small">
                        <div class="section-title">Paciente</div>
                        <dl class="row mb-3">
                            <dt class="col-5">Nombre</dt><dd class="col-7">{{ $case->patient?->display_name }}</dd>
                            <dt class="col-5">Cédula</dt><dd class="col-7">{{ $case->patient?->document_number ?: '—' }}</dd>
                            <dt class="col-5">Sexo</dt><dd class="col-7">{{ $case->patient?->sex_label }}</dd>
                            <dt class="col-5">Edad</dt><dd class="col-7">{{ $case->patient?->age_label }}</dd>
                        </dl>

                        <div class="section-title">Fechas</div>
                        <div class="mb-2">
                            <label class="form-label">Recibido</label>
                            <input type="date" name="received_at" class="form-control form-control-sm"
                                   value="{{ old('received_at', $case->received_at?->format('Y-m-d')) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Enviado</label>
                            <input type="date" name="sent_at" class="form-control form-control-sm"
                                   value="{{ old('sent_at', $case->sent_at?->format('Y-m-d')) }}">
                        </div>

                        <div class="section-title">Patólogos</div>
                        <div class="mb-2">
                            <label class="form-label">Titular</label>
                            <select name="pathologist_1" class="form-select form-select-sm">
                                <option value="">—</option>
                                @foreach ($pathologists as $pathologist)
                                    <option value="{{ $pathologist->name }}"
                                        @selected(old('pathologist_1', $case->assignments->firstWhere('role', 1)?->pathologist_name) === $pathologist->name)>
                                        {{ $pathologist->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Cosignante</label>
                            <select name="pathologist_2" class="form-select form-select-sm">
                                <option value="">—</option>
                                @foreach ($pathologists as $pathologist)
                                    <option value="{{ $pathologist->name }}"
                                        @selected(old('pathologist_2', $case->assignments->firstWhere('role', 2)?->pathologist_name) === $pathologist->name)>
                                        {{ $pathologist->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna derecha: el informe --}}
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="section-title">Muestra</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Material remitido</label>
                                <input type="text" name="material" class="form-control"
                                       value="{{ old('material', $report?->material) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Diagnóstico clínico</label>
                                <input type="text" name="clinical_diagnosis" class="form-control"
                                       value="{{ old('clinical_diagnosis', $report?->clinical_diagnosis) }}">
                            </div>
                            @if ($biopsy)
                                <div class="col-md-6">
                                    <label class="form-label">Sitio de la lesión</label>
                                    <input type="text" name="lesion_site" class="form-control"
                                           value="{{ old('lesion_site', $report?->lesion_site) }}">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="section-title">Descripción macroscópica</div>
                        <textarea name="macro_description" id="macro_description" rows="5" class="form-control mb-2">{{ old('macro_description', $report?->macro_description) }}</textarea>
                        @if ($macroTemplates->isNotEmpty())
                            <select class="form-select form-select-sm" data-target="macro_description">
                                <option value="">Insertar plantilla macroscópica…</option>
                                @foreach ($macroTemplates as $template)
                                    <option value="{{ $template->content }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                @if ($biopsy)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="section-title">Descripción microscópica</div>
                            <textarea name="micro_description" id="micro_description" rows="6" class="form-control mb-2">{{ old('micro_description', $report?->micro_description) }}</textarea>
                            @if ($microTemplates->isNotEmpty())
                                <select class="form-select form-select-sm" data-target="micro_description">
                                    <option value="">Insertar plantilla microscópica…</option>
                                    @foreach ($microTemplates as $template)
                                        <option value="{{ $template->content }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="section-title">Diagnóstico microscópico</div>
                            <textarea name="micro_diagnosis" id="micro_diagnosis" rows="4" class="form-control mb-2">{{ old('micro_diagnosis', $report?->micro_diagnosis) }}</textarea>
                            @if ($dxTemplates->isNotEmpty())
                                <select class="form-select form-select-sm" data-target="micro_diagnosis">
                                    <option value="">Insertar diagnóstico…</option>
                                    @foreach ($dxTemplates as $template)
                                        <option value="{{ $template->content }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="section-title">Descripción citológica</div>

                            @if ($diagnoses->isNotEmpty())
                                <select id="diagnosisPicker" class="form-select form-select-sm mb-2">
                                    <option value="">Insertar diagnóstico del catálogo…</option>
                                    @foreach ($diagnoses as $diagnosis)
                                        <option value="{{ $diagnosis->code }}"
                                                data-description="{{ $diagnosis->description }}"
                                                data-diagnosis="{{ $diagnosis->diagnosis }}">
                                            {{ $diagnosis->code }} — {{ \Illuminate\Support\Str::limit($diagnosis->description, 90) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            <textarea name="cyto_description" id="cyto_description" rows="5" class="form-control mb-2">{{ old('cyto_description', $report?->cyto_description) }}</textarea>

                            @if ($annexes->isNotEmpty())
                                <select id="annexPicker" class="form-select form-select-sm">
                                    <option value="">Agregar anexo…</option>
                                    @foreach ($annexes as $annex)
                                        <option value="{{ $annex->text }}">{{ $annex->code }} — {{ \Illuminate\Support\Str::limit($annex->text, 90) }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="section-title">Diagnóstico citológico</div>
                            <textarea name="cyto_diagnosis" id="cyto_diagnosis" rows="4" class="form-control">{{ old('cyto_diagnosis', $report?->cyto_diagnosis) }}</textarea>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="section-title">Calidad de la muestra</div>
                            <textarea name="quality" rows="2" class="form-control">{{ old('quality', $report?->quality) }}</textarea>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="section-title">Índice de valoración hormonal</div>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label">Superficiales</label>
                                    <input type="number" name="superficial" min="0" max="1000" class="form-control"
                                           value="{{ old('superficial', $report?->superficial) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Intermedias</label>
                                    <input type="number" name="intermediate" min="0" max="1000" class="form-control"
                                           value="{{ old('intermediate', $report?->intermediate) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Parabasales</label>
                                    <input type="number" name="parabasal" min="0" max="1000" class="form-control"
                                           value="{{ old('parabasal', $report?->parabasal) }}">
                                </div>
                            </div>
                            <div class="mt-2">
                                <label class="form-label">Conclusión</label>
                                <textarea name="conclusion" rows="3" class="form-control">{{ old('conclusion', $report?->conclusion) }}</textarea>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="section-title">Notas</div>
                        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $report?->notes) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="muted small" id="saveState">Sin cambios</span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('cases.show', $case) }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar informe
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    // Plantillas: reemplazan el contenido del textarea destino.
    document.querySelectorAll('select[data-target]').forEach(function (select) {
        select.addEventListener('change', function () {
            if (!this.value) return;
            var target = document.getElementById(this.dataset.target);
            if (target) {
                target.value = this.value;
                target.dispatchEvent(new Event('input'));
                target.focus();
            }
            this.value = '';
        });
    });

    // Catálogo de diagnósticos: llena descripción y diagnóstico.
    var diagnosisPicker = document.getElementById('diagnosisPicker');
    if (diagnosisPicker) {
        diagnosisPicker.addEventListener('change', function () {
            var opt = this.options[this.selectedIndex];
            if (!opt || !opt.value) return;
            var desc = document.getElementById('cyto_description');
            var diag = document.getElementById('cyto_diagnosis');
            if (desc) { desc.value = opt.dataset.description || ''; desc.dispatchEvent(new Event('input')); }
            if (diag) { diag.value = opt.dataset.diagnosis || ''; diag.dispatchEvent(new Event('input')); }
            this.value = '';
        });
    }

    // Anexos: se agregan al final de la descripción citológica.
    var annexPicker = document.getElementById('annexPicker');
    if (annexPicker) {
        annexPicker.addEventListener('change', function () {
            if (!this.value) return;
            var area = document.getElementById('cyto_description');
            if (area) {
                var text = this.value.trim().replace(/\.$/, '');
                var current = area.value.trim().replace(/\.$/, '');
                area.value = current ? (current + '. ' + text + '.') : (text + '.');
                area.dispatchEvent(new Event('input'));
                area.scrollTop = area.scrollHeight;
            }
            this.value = '';
        });
    }

    // Aviso de cambios sin guardar + Ctrl/Cmd+S.
    var form = document.getElementById('reportForm');
    var state = document.getElementById('saveState');
    var dirty = false;

    form.addEventListener('input', function () {
        dirty = true;
        state.textContent = 'Cambios sin guardar';
        state.classList.add('text-warning');
    });

    form.addEventListener('submit', function () { dirty = false; });

    window.addEventListener('beforeunload', function (e) {
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            form.requestSubmit();
        }
    });
})();
</script>
@endpush
