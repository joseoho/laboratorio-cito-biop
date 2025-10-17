@extends('layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1>Información del Paciente</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('pacientes.edit', $paciente->id) }}" class="btn btn-warning">Editar</a>
            <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>

    <!-- Frame: Identificación -->
    <div class="frame mb-4 p-3 border rounded">
        <h3 class="mb-4">Identificación</h3>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Fecha de Ingreso:</strong></label>
                <p class="form-control-plaintext">{{ $paciente->FechaApertura }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Cédula Número:</strong></label>
                <p class="form-control-plaintext">{{ $paciente->CI }}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Nombres:</strong></label>
                <p class="form-control-plaintext">{{ $paciente->Nombre }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Sexo:</strong></label>
                <p class="form-control-plaintext">{{ $paciente->Sexo }}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Dirección:</strong></label>
                <p class="form-control-plaintext">{{ $paciente->Dirección ?? 'No especificada' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Teléfono:</strong></label>
                <p class="form-control-plaintext">{{ $paciente->Teléfono ?? 'No especificado' }}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Edad:</strong></label>
                <p class="form-control-plaintext">{{ $paciente->Edad }} años</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Procedencia:</strong></label>
                <p class="form-control-plaintext">{{ $paciente->procedencia->Codigo ?? $paciente->Procedencia }}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Médico:</strong></label>
                <p class="form-control-plaintext">
                    {{ $paciente->medico->Codigo ?? $paciente->Dr }}
                    @if($paciente->medico->Codigo && $paciente->Dr)
                        ({{ $paciente->medico->especialidad }})
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Correo Electrónico:</strong></label>
                <p class="form-control-plaintext">{{ $paciente->Mail }}</p>
            </div>
        </div>
    </div>

    <!-- Frame: Biopsias y Citologías -->
    <div class="frame mb-4 p-3 border rounded">
        <h3 class="mb-4">Biopsias y Citologías</h3>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Biopsia:</strong></label>
                <p class="form-control-plaintext">{{ $paciente->NB ?? 'No asignada' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Citología:</strong></label>
                <p class="form-control-plaintext">{{ $paciente->NC ?? 'No asignada' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection