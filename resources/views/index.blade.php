
@extends('layouts.layout')

@section('title', 'LABORATORIO BECOMARCA')

@section('content')
<style>
    .background {
        background-image: url("{{ asset('assets/images/logo.png') }}");
        background-size: cover; 
        background-position: center;
        background-repeat: no-repeat;
        height: 100vh; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        color: white; 
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        position: relative;
    }
    
    /* Opcional: Si quieres que la imagen sea más tenue como marca de agua */
    .background::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: url("{{ asset('assets/images/tu-imagen.jpg') }}");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        opacity: 0.3; /* Ajusta la transparencia aquí */
        z-index: -1;
    }
</style>

<div class="background">
    <h1></h1>
</div>
@endsection