<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laboratorio') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background:#f4f5f7; font-family:'Segoe UI', Arial, sans-serif; }
        .navbar-brand { font-weight:600; letter-spacing:.3px; }
        .card { border:0; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .table thead th { background:#212529; color:#fff; font-size:.8rem; text-transform:uppercase; letter-spacing:.4px; }
        .table td { vertical-align:middle; }
        .badge-case { font-family:Consolas, monospace; font-weight:600; }
        .search-box { background:#fff; border-radius:.5rem; padding:1rem; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .sticky-panel { position:sticky; top:1rem; }
        .muted { color:#6c757d; }
        .section-title { font-size:.75rem; text-transform:uppercase; letter-spacing:.6px; color:#6c757d; margin-bottom:.5rem; }
        .flag { font-size:.7rem; }
        @@media print {
            nav, .alert, .no-print { display: none !important; }
            body { background:#fff; }
            main { padding:0 !important; }
            .card { box-shadow:none; border:1px solid #ccc; }
        }
    </style>
    @stack('head')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="{{ route('patients.index') }}">
            <i class="fas fa-microscope me-2"></i>{{ config('app.name') }}
        </a>
        <div class="d-flex align-items-center gap-3">
            <a class="nav-link text-white-50" href="{{ route('patients.index') }}">Pacientes</a>
            <a class="nav-link text-white-50" href="{{ route('cases.index') }}">Estudios</a>
            @auth
                @if (auth()->user()->canViewReports())
                    <a class="nav-link text-white-50" href="{{ route('reports.index') }}">Reportes</a>
                @endif
                @if (auth()->user()->isAdmin())
                    <a class="nav-link text-white-50" href="{{ route('quarantine.index') }}">Cuarentena</a>
                    <a class="nav-link text-white-50" href="{{ route('backups.index') }}">Respaldos</a>
                    <a class="nav-link text-white-50" href="{{ route('signature.index') }}">Firma</a>
                    <a class="nav-link text-white-50" href="{{ route('users.index') }}">Usuarios</a>
                @endif
                <span class="text-white-50 small">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">Salir</button>
                </form>
            @endauth
        </div>
    </div>
</nav>

<main class="container-fluid px-4 pb-5">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
