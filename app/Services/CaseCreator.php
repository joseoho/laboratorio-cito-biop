<?php

namespace App\Services;

use App\Enums\CaseStatus;
use App\Enums\CaseType;
use App\Models\ClinicalCase;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class CaseCreator
{
    public function __construct(private CaseNumberGenerator $numbers)
    {
    }

    /**
     * Crea el estudio y su informe vacío para que el editor tenga dónde escribir.
     */
    public function create(
        Patient $patient,
        CaseType $type,
        string $caseCode,
        ?string $receivedAt = null,
        ?string $material = null,
        ?string $clinicalDiagnosis = null,
    ): ClinicalCase {
        $parsed = $this->numbers->parse($type, $caseCode);

        return DB::transaction(function () use ($patient, $type, $caseCode, $receivedAt, $material, $clinicalDiagnosis, $parsed) {
            /** @var ClinicalCase $case */
            $case = $patient->cases()->create([
                'type' => $type,
                'case_code' => $caseCode,
                'sequence_no' => $parsed[0] ?? null,
                'year_no' => $parsed[1] ?? null,
                'status' => CaseStatus::Received,
                'received_at' => $receivedAt ?? now()->toDateString(),
                'legacy_source' => null,
                'legacy_row_id' => null,
            ]);

            $report = [
                'case_id' => $case->id,
                'material' => $material,
                'clinical_diagnosis' => $clinicalDiagnosis,
            ];

            if ($type === CaseType::Biopsy) {
                $case->histology()->create($report);
            } else {
                $case->cytology()->create($report);
            }

            return $case;
        });
    }
}
