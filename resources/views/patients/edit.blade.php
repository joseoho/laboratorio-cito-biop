@extends('layouts.core')

@section('title', 'Editar ' . $patient->display_name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Editar paciente</h4>
            <span class="muted small">{{ $patient->full_name }} · Recibo {{ $patient->id }}</span>
        </div>
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-secondary">Volver</a>
    </div>

    @include('patients._form', ['patient' => $patient])
@endsection
