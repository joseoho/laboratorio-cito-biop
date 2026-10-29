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

    /** Campos que deben existir para poder firmar. */
    public function missingForSignature(): array
    {
        return array_keys(array_filter([
            'Descripción macroscópica' => blank($this->macro_description),
            'Descripción microscópica' => blank($this->micro_description),
            'Diagnóstico microscópico' => blank($this->micro_diagnosis),
            'Sitio de la lesión' => blank($this->lesion_site),
        ]));
    }
}
