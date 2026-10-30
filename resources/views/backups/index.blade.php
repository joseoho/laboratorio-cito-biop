@extends('layouts.core')

@section('title', 'Respaldos')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h4 class="mb-0">Respaldos</h4>
            <span class="muted small">Copia comprimida de la base operativa (solo administrador)</span>
        </div>
        <form method="POST" action="{{ route('backups.store') }}">
            @csrf
            <button class="btn btn-sm btn-primary" onclick="return confirm('¿Generar un respaldo ahora?')">
                <i class="fas fa-database me-1"></i>Generar respaldo ahora
            </button>
        </form>
    </div>

    <div class="alert alert-secondary small">
        <strong>Ojo:</strong> un respaldo en el mismo equipo no protege contra un daño del disco.
        Conviene programarlo a diario y copiar el archivo a otro medio.
        <div class="mt-2">
            Programar a las 20:00:
            <code>schtasks /Create /TN "Laboratorio backup" /SC DAILY /ST 20:00 /TR "powershell.exe -NoProfile -ExecutionPolicy Bypass -File {{ base_path('scripts\backup_db.ps1') }}"</code>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th style="width:110px" class="text-end">Tamaño</th>
                        <th style="width:170px">Generado</th>
                        <th style="width:110px"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($files as $file)
                    <tr>
                        <td class="small">{{ $file['name'] }}</td>
                        <td class="text-end">{{ number_format($file['size'] / 1048576, 1, ',', '.') }} MB</td>
                        <td>{{ $file['modified'] }}</td>
                        <td class="text-end">
                            <a href="{{ route('backups.download', $file['name']) }}" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-download me-1"></i>Descargar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-4 muted">Todavía no hay respaldos.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body small">
            <div class="section-title">Cómo se restaura</div>
            <code class="d-block">gunzip -c {{ $dir }}\lapt_core-AAAAMMDD-HHMMSS.sql.gz | C:\xampp\mysql\bin\mysql.exe -u root lapt_core</code>
            <div class="muted mt-2">
                Se conservan los últimos 14 respaldos. Un respaldo que nunca se restauró no cuenta como respaldo:
                conviene probar la restauración en una base de prueba al menos una vez.
            </div>
        </div>
    </div>
@endsection
