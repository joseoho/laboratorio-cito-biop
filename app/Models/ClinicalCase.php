<?php

namespace App\Models;

use App\Enums\CaseStatus;
use App\Enums\CaseType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClinicalCase extends Model
{
    protected $table = 'cases';

    public $timestamps = false;

    protected $fillable = [
        'patient_id', 'type', 'case_code', 'sequence_no', 'year_no', 'status',
        'received_at', 'sent_at', 'completed_at', 'legacy_source', 'legacy_row_id', 'flags',
    ];

    protected $casts = [
        'type' => CaseType::class,
        'status' => CaseStatus::class,
        'received_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'sequence_no' => 'integer',
        'year_no' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function histology(): HasOne
    {
        return $this->hasOne(HistologyReport::class, 'case_id');
    }

    public function cytology(): HasOne
    {
        return $this->hasOne(CytologyReport::class, 'case_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CaseAssignment::class, 'case_id')->orderBy('role');
    }

    public function report(): HistologyReport|CytologyReport|null
    {
        return $this->type === CaseType::Biopsy ? $this->histology : $this->cytology;
    }

    /** Campos que faltan para que el informe quede terminado y emitido. */
    public function missingForEmission(): array
    {
        $report = $this->report();

        return $report ? $report->missingForEmission() : ['Informe'];
    }

    public function isComplete(): bool
    {
        return $this->missingForEmission() === [];
    }

    /**
     * Solo se imprime lo emitido. Los casos históricos siguen imprimibles
     * aunque estén "En proceso": no se revalida el pasado.
     */
    public function isPrintable(): bool
    {
        return $this->status === CaseStatus::Delivered || $this->legacy_source !== null;
    }

    public function isBiopsy(): bool
    {
        return $this->type === CaseType::Biopsy;
    }

    public function codeLabel(): string
    {
        if (! $this->case_code) {
            return 'S/N';
        }

        return $this->type->prefix() . '-' . $this->case_code;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('case_code', 'like', "%{$term}%")
              ->orWhereHas('patient', fn (Builder $p) => $p
                  ->where('full_name', 'like', "%{$term}%")
                  ->orWhere('document_number', 'like', "%{$term}%"));
        });
    }
}
