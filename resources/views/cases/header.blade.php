@extends('layouts.core')

@section('title', 'Datos del estudio ' . $case->codeLabel())

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Datos del estudio</h4>
            <span class="muted small">{{ $case->patient?->display_name }} · Recibo {{ $case->patient?->id }}</span>
        </div>
        <a href="{{ route('cases.show', $case) }}" class="btn btn-sm btn-outline-secondary">Volver</a>
    </div>

    <form method="POST" action="{{ route('cases.header.update', $case) }}">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <input type="text" class="form-control" value="{{ $case->type->label() }}" disabled>
                        <div class="form-text">El tipo no se puede cambiar: el informe ya tiene esa estructura.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Número</label>
                        <input type="text" name="case_code" class="form-control @error('case_code') is-invalid @enderror"
                               value="{{ old('case_code', $case->case_code) }}" inputmode="numeric" autocomplete="off">
                        @error('case_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">
                            {{ $case->isBiopsy() ? '6 dígitos' : '7 dígitos' }}: secuencia + año.
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha de recepción</label>
                        <input type="date" name="received_at" class="form-control @error('received_at') is-invalid @enderror"
                               value="{{ old('received_at', $case->received_at?->format('Y-m-d')) }}">
                        @error('received_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info small">
            Corregir el número recalcula la secuencia y el año. Si el número ya existe para ese tipo,
            el sistema lo rechaza.
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('cases.show', $case) }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Guardar datos
            </button>
        </div>
    </form>
@endsection
