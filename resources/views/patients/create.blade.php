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

    @include('patients._form')
@endsection
