<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CytologyReport extends Model
{
    protected $table = 'cytology_reports';

    protected $primaryKey = 'case_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'case_id', 'material', 'clinical_diagnosis', 'macro_description',
        'cyto_description', 'cyto_diagnosis', 'quality', 'notes',
        'superficial', 'intermediate', 'parabasal', 'conclusion',
    ];

    protected $casts = [
        'superficial' => 'integer',
        'intermediate' => 'integer',
        'parabasal' => 'integer',
    ];

    public function clinicalCase(): BelongsTo
    {
        return $this->belongsTo(ClinicalCase::class, 'case_id');
    }

    public function hasHormonalIndex(): bool
    {
        return ($this->superficial ?? 0) > 0
            || ($this->intermediate ?? 0) > 0
            || ($this->parabasal ?? 0) > 0;
    }

    /** Campos que deben existir para poder firmar. */
    public function missingForSignature(): array
    {
        return array_keys(array_filter([
            'Descripción citológica' => blank($this->cyto_description),
            'Diagnóstico citológico' => blank($this->cyto_diagnosis),
            'Calidad de la muestra' => blank($this->quality),
        ]));
    }
}
