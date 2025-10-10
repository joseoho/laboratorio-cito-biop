<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        /* Estilos para el menú lateral */
        #sidebar {
            width: 250px;
            height: 100vh;
            background-color: #343a40; /* Fondo oscuro */
            color: #ffffff; /* Texto blanco */
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto; /* Scroll si el contenido es largo */
        }

        #sidebar .sidebar-header {
            padding: 20px;
            background-color: #23272b; /* Fondo más oscuro para el encabezado */
            text-align: center;
        }

        #sidebar ul.components {
            padding: 20px 0;
        }

        #sidebar ul li a {
            padding: 10px 20px;
            color: #ffffff; /* Texto blanco */
            display: block;
            text-decoration: none;
        }

        #sidebar ul li a:hover {
            background-color: #495057; /* Fondo gris al pasar el mouse */
        }

        #sidebar ul li.active > a {
            background-color: #007bff; /* Fondo azul para la opción activa */
        }

        #sidebar ul li ul li a {
            padding-left: 30px; /* Sangría para subopciones */
        }

        /* Contenido principal */
        #content {
            margin-left: 250px; /* Espacio para el menú lateral */
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Menú lateral -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>LABORATORIO BECOMARCA</h3>
        </div>
        <ul class="list-unstyled components">
            <li>
                <a href="#listadosSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Listados</a>
                <ul class="collapse list-unstyled" id="listadosSubmenu">
                    <li><a href="#">Relación de Ingresos</a></li>
                    <li><a href="#">Exámenes</a></li>
                    <li><a href="#">Listado de Exámenes</a></li>
                    <li><a href="#">Patólogo</a></li>
                    <li><a href="#">Procedencia</a></li>
                    <li><a href="#">Médico</a></li>
                    <li><a href="#">Plantillas</a></li>
                </ul>
            </li>
            <!-- Puedes agregar más opciones aquí -->
        </ul>
    </nav>

    <!-- Contenido principal -->
    <!-- <div id="content">
        <h1>Bienvenido al Laboratorio Becomarca</h1>
        <p>Este es el contenido principal de la página.</p>
    </div> -->

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>