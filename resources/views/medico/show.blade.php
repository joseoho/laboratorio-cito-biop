@extends('layouts.layout')

@section('content')
<div class="container">
    <h1>{{ $finca->nombre }}</h1>
    <p><strong>Ubicación:</strong> {{ $finca->ubicacion }}</p>
    <p><strong>Área:</strong> {{ $finca->area }} m²</p>
    <p><strong>Descripción:</strong> {{ $finca->descripcion }}</p>

    <h2>Lotes por Propósito</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Propósito</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lotesPorProposito as $lote)
                <tr>
                    <td>{{ $lote->proposito }}</td>
                    <td>{{ $lote->total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Últimos Movimientos de Animales</h2>
    <ul class="list-group">
        @foreach($ultimosMovimientos as $movimiento)
            <li class="list-group-item">
                <strong>Animal:</strong> {{ $movimiento->animal->nombre ?? 'Desconocido' }}<br>
                <strong>Fecha:</strong> {{ $movimiento->fecha }}<br>
                <strong>Descripción:</strong> {{ $movimiento->descripcion }}
            </li>
        @endforeach
    </ul>

    <h2>Resumen Financiero</h2>
    <p><strong>Ingresos Totales:</strong> ${{ number_format($resumenFinanciero['ingresos'], 2) }}</p>
    <p><strong>Egresos Totales:</strong> ${{ number_format($resumenFinanciero['egresos'], 2) }}</p>
    <p><strong>Balance:</strong> ${{ number_format($resumenFinanciero['ingresos'] - $resumenFinanciero['egresos'], 2) }}</p>

    <div class="mt-4">
        <a href="{{ route('fincas.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-left"></i> Volver al listado
        </a>
    </div>
</div>
@endsection
