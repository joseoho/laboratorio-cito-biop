<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function __construct(private BackupService $backups)
    {
    }

    public function index(): View
    {
        return view('backups.index', [
            'files' => $this->backups->files(),
            'dir' => $this->backups->dir(),
        ]);
    }

    public function store(AuditLogger $audit): RedirectResponse
    {
        try {
            $result = $this->backups->run();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('backups.index')
                ->with('error', 'No se pudo generar el respaldo: ' . $e->getMessage());
        }

        $latest = $result['latest'];

        $audit->logRaw('backup.created', 'backup', null, [
            'file' => $latest['name'] ?? null,
            'size' => $latest['size'] ?? null,
        ]);

        return redirect()
            ->route('backups.index')
            ->with('success', 'Respaldo generado: ' . ($latest['name'] ?? 'archivo creado') . '.');
    }

    public function download(Request $request, string $file): BinaryFileResponse
    {
        abort_unless(preg_match('/^lapt_core-\d{8}-\d{6}\.sql\.gz$/', $file) === 1, 404);

        $path = $this->backups->dir() . DIRECTORY_SEPARATOR . $file;

        abort_unless(is_file($path), 404, 'El respaldo no existe.');

        return response()->download($path);
    }
}
