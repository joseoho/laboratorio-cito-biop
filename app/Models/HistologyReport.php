<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistologyReport extends Model
{
    protected $table = 'histology_reports';

    protected $primaryKey = 'case_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'case_id', 'material', 'clinical_diagnosis', 'lesion_site',
        'macro_description', 'micro_description', 'micro_diagnosis', 'notes',
    ];

    public function clinicalCase(): BelongsTo
    {
        return $this->belongsTo(ClinicalCase::class, 'case_id');
    }

    /**
     * Campos que definen un informe terminado. Se exigen solo los que el
     * laboratorio llena de verdad: material 98,7%, macro 99,3% y diagnóstico
     * microscópico 99%. La descripción microscópica (7% del histórico), el
     * sitio de la lesión (87%) y el diagnóstico clínico (76%) quedan opcionales.
     */
    public function missingForEmission(): array
    {
        return array_keys(array_filter([
            'Material remitido' => blank($this->material),
            'Descripción macroscópica' => blank($this->macro_description),
            'Diagnóstico microscópico' => blank($this->micro_diagnosis),
        ]));
    }
}
