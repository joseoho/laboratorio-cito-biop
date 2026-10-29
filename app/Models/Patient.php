<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $table = 'patients';

    protected $fillable = [
        'legacy_id', 'document_number', 'document_is_placeholder', 'full_name', 'sex',
        'legacy_sex_raw', 'age_at_entry', 'legacy_age_text', 'birth_date',
        'phone_code', 'phone', 'address', 'email', 'doctor_id', 'origin_id',
        'created_at', 'flags',
    ];

    protected $casts = [
        'document_is_placeholder' => 'boolean',
        'age_at_entry' => 'integer',
        'birth_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function cases(): HasMany
    {
        return $this->hasMany(ClinicalCase::class)->orderByDesc('received_at')->orderByDesc('id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Origin::class);
    }

    /**
     * Búsqueda operativa: cédula/nombre (por defecto), número de biopsia o de citología.
     */
    public function scopeSearch(Builder $query, ?string $term, string $type = 'ci'): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return match ($type) {
            'nb' => $query->whereHas('cases', fn (Builder $q) => $q
                ->where('type', 'biopsy')
                ->where('case_code', 'like', "%{$term}%")),
            'nc' => $query->whereHas('cases', fn (Builder $q) => $q
                ->where('type', 'cytology')
                ->where('case_code', 'like', "%{$term}%")),
            default => $query->where(function (Builder $q) use ($term) {
                $q->where('document_number', 'like', "%{$term}%")
                  ->orWhere('full_name', 'like', "%{$term}%");
            }),
        };
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?: 'Sin nombre';
    }

    public function getAgeLabelAttribute(): string
    {
        if ($this->age_at_entry) {
            return $this->age_at_entry . ' años';
        }

        return $this->legacy_age_text ?: '—';
    }

    public function getSexLabelAttribute(): string
    {
        return match ($this->sex) {
            'F' => 'Femenino',
            'M' => 'Masculino',
            'X' => 'Otro',
            default => '—',
        };
    }
}
