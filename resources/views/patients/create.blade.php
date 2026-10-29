@extends('layouts.core')

@section('title', 'Nuevo paciente')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Nuevo paciente</h4>
            <span class="muted small">Registro de recepción. Los estudios se crean desde la ficha del paciente.</span>
        </div>
        <a href="{{ route('patients.index') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
    </div>

    <form method="POST" action="{{ route('patients.store') }}">
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
                <div class="section-title">Identificación</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                               value="{{ old('full_name') }}" required autofocus>
                        @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cédula</label>
                        <input type="text" name="document_number" class="form-control @error('document_number') is-invalid @enderror"
                               value="{{ old('document_number') }}" placeholder="V-12345678">
                        @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Edad</label>
                        <input type="number" name="age_at_entry" class="form-control @error('age_at_entry') is-invalid @enderror"
                               value="{{ old('age_at_entry') }}" min="0" max="120">
                        @error('age_at_entry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sexo</label>
                        <select name="sex" class="form-select">
                            <option value="">—</option>
                            <option value="F" @selected(old('sex') === 'F')>Femenino</option>
                            <option value="M" @selected(old('sex') === 'M')>Masculino</option>
                            <option value="X" @selected(old('sex') === 'X')>Otro / no especificado</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="section-title">Contacto</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" maxlength="20">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}" maxlength="200">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="section-title">Referencia</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Médico que remite</label>
                        <select name="doctor_id" class="form-select">
                            <option value="">—</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}" @selected((string) old('doctor_id') === (string) $doctor->id)>
                                    {{ $doctor->legacy_code }} — {{ $doctor->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">{{ $doctors->count() }} médicos en el catálogo.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Procedencia</label>
                        <select name="origin_id" class="form-select">
                            <option value="">—</option>
                            @foreach ($origins as $origin)
                                <option value="{{ $origin->id }}" @selected((string) old('origin_id') === (string) $origin->id)>
                                    {{ $origin->legacy_code }}{{ $origin->price_list ? ' — ' . $origin->price_list : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Registrar paciente
            </button>
        </div>
    </form>
@endsection
