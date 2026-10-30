<?php

namespace App\Services;

use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Respaldo de la base operativa reutilizando scripts/backup_db.ps1
 * (mysqldump + gzip + rotación) y listado de los respaldos existentes.
 */
class BackupService
{
    public function dir(): string
    {
        return storage_path('backups');
    }

    /** @return Collection<int, array{name:string,size:int,modified:string}> */
    public function files(): Collection
    {
        if (! is_dir($this->dir())) {
            return collect();
        }

        return collect(glob($this->dir() . DIRECTORY_SEPARATOR . 'lapt_core-*.sql.gz') ?: [])
            ->map(fn (string $path) => [
                'name' => basename($path),
                'size' => (int) filesize($path),
                'modified' => date('Y-m-d H:i:s', (int) filemtime($path)),
            ])
            ->sortByDesc('modified')
            ->values();
    }

    /**
     * @return array{output:string, latest:array{name:string,size:int,modified:string}|null}
     */
    public function run(): array
    {
        $script = base_path('scripts' . DIRECTORY_SEPARATOR . 'backup_db.ps1');

        if (! is_file($script)) {
            throw new RuntimeException('No se encontró scripts/backup_db.ps1.');
        }

        $command = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($script) . ' 2>&1';

        $output = [];
        $code = 0;
        exec($command, $output, $code);

        if ($code !== 0) {
            throw new RuntimeException('El respaldo falló: ' . implode(' ', $output));
        }

        return [
            'output' => implode("\n", $output),
            'latest' => $this->files()->first(),
        ];
    }
}
