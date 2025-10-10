<!-- base.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LABORATORIO BECOMARCA</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para íconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Fondo gris suave */
            font-family: 'Arial', sans-serif;
        }

        .container {
            background-color: #ffffff; /* Fondo blanco para el contenido */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra suave */
            margin-top: 30px;
        }

        h1 {
            color: #343a40; /* Color oscuro para el título */
            font-weight: bold;
            margin-bottom: 30px;
        }

        .frame {
            border: 1px solid #dee2e6; /* Borde gris claro */
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f8f9fa; /* Fondo gris suave para los frames */
        }

        .frame h3 {
            color: #495057; /* Color gris oscuro para los títulos de los frames */
            margin-bottom: 20px;
            font-weight: 600;
        }

        label {
            color: #495057; /* Color gris oscuro para las etiquetas */
            font-weight: 500;
        }

        .form-control {
            border-radius: 4px;
            border: 1px solid #ced4da; /* Borde gris para los inputs */
        }

        .btn-outline-secondary {
            border-radius: 4px;
            border: 1px solid #6c757d; /* Borde gris para los botones */
            color: #6c757d; /* Color gris para el texto de los botones */
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d; /* Fondo gris al pasar el mouse */
            color: #ffffff; /* Texto blanco al pasar el mouse */
        }

        .btn-add {
            background-color: #28a745; /* Color verde para el botón de agregar */
            color: #ffffff; /* Texto blanco */
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
        }

        .btn-add:hover {
            background-color: #218838; /* Color verde oscuro al pasar el mouse */
        }

        .btn-show {
            background-color: #17a2b8; /* Color azul para el botón de mostrar */
            color: #ffffff; /* Texto blanco */
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
        }

        .btn-show:hover {
            background-color: #138496; /* Color azul oscuro al pasar el mouse */
        }
    </style>
  
  
</head>
<body>

    <!-- Menú lateral -->
    <nav id="sidebar">
        <!-- Código del menú -->
    </nav>

    <!-- Contenido principal -->
    <div id="content">
        @yield('content') <!-- Aquí se insertará el contenido de cada vista -->
        <div class="container">
        <!-- Título de la página -->
        <h1 class="text-center">LABORATORIO BECOMARCA</h1>
      
                <div class="col-md-6">
                   <input type="text" id="idPaciente" class="form-control ml-3 w-auto" disabled>
                </div>
       
            
        <!-- Frame 1: Barra de búsqueda -->
        <div class="frame">
            <h3>Buscar</h3>
            <div class="input-group">
                <input type="text" id ="valorBusqueda" class="form-control" placeholder="Ingrese su búsqueda">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" id="btnci/nom" onclick="buscarPorCI()" type="button">CI/NOM</button>
                    <button class="btn btn-outline-secondary" id="btnrb" type="button">RB</button>
                    <button class="btn btn-outline-secondary" id="btnnb" onclick="buscarPorNB()"type="button">NB</button>
                    <button class="btn btn-outline-secondary" id="btnnc" onclick="buscarPorNC()" type="button">NC</button>
                </div>
            </div>
        </div>
                            
                                @include('form.formprincipal')
                                 
						
    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </div>

</body>

<script src="{{ asset('assets/js/llenarmedicos.js')}}"></script>
<script src="{{ asset('assets/js/llenarprocedencia.js')}}"></script>
<script src="{{ asset('assets/js/buscarxCI.js')}}"></script>
<script src="{{ asset('assets/js/buscarxNC.js')}}"></script>
<script src="{{ asset('assets/js/buscarxNB.js')}}"></script>
<script src="{{ asset('assets/js/llenarForm.js')}}"></script>
<script src="{{ asset('assets/js/guardaenformprincipal.js')}}"></script>


</html>

<!-- vista.blade.php -->
@extends('layout.layout')

<!-- @section('content')
    <h1>Bienvenido a la Vista</h1>
    <p>Este es el contenido específico de esta vista.</p>
@endsection -->




