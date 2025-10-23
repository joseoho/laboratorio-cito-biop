
@extends('layouts.layout')

@section('title', 'Fincas')

@section('content')
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>PAGINA FINCA</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/dashboard/">

    

    <!-- Bootstrap core CSS -->
<link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>

    
    <!-- Custom styles for this template -->
    <link href="dashboard.css" rel="stylesheet">
  </head>
  <div class="row">
                  <div class="col-12">
                    
                 <form action="{{route('fincas.store')}}" method="POST" name="fincas"> 
                    @csrf
                        @include('fincas.form')
	                     <!-- <div class="form-group mr-2">
                          <a href="/Empleado" class="btn btn-light">Regresar</a>
                          <button type="submit" class="btn btn-primary">Guardar</button>
							        </div> -->
				         </form>
                  	
                </div>
                @endsection