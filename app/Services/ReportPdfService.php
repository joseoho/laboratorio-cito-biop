<?php

namespace App\Services;

use App\Models\ClinicalCase;
use App\Models\ReportPdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

/**
 * Genera y archiva el PDF del informe emitido: copia inmutable con hash
 * SHA-256 y número de versión. Es el respaldo legal del acto médico.
 */
class ReportPdfService
{
    public function generate(ClinicalCase $case, ?int $actorId = null): ReportPdf
    {
        $case->loadMissing(['patient', 'assignments.pathologist', 'histology', 'cytology']);

        $html = View::make('reports.pdf', ['case' => $case])->render();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', base_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $contents = $dompdf->output();

        $dir = storage_path('app/private/reports');
        File::ensureDirectoryExists($dir);

        $base = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($case->case_code ?: 'SN'));
        $name = sprintf('%s-%s-%d-%s.pdf', $case->type->value, $base, $case->id, now()->format('YmdHis'));

        File::put($dir . DIRECTORY_SEPARATOR . $name, $contents);

        $version = ((int) ReportPdf::query()->where('case_id', $case->id)->max('version')) + 1;

        return ReportPdf::create([
            'case_id' => $case->id,
            'path' => $name,
            'hash' => hash('sha256', $contents),
            'version' => $version,
            'generated_by' => $actorId,
        ]);
    }

    public function path(ReportPdf $pdf): string
    {
        return storage_path('app/private/reports/' . $pdf->path);
    }
}
