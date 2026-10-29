<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseAssignment extends Model
{
    protected $table = 'case_assignments';

    public $timestamps = false;

    protected $fillable = ['case_id', 'pathologist_id', 'pathologist_name', 'role'];

    public function clinicalCase(): BelongsTo
    {
        return $this->belongsTo(ClinicalCase::class, 'case_id');
    }

    public function pathologist(): BelongsTo
    {
        return $this->belongsTo(Pathologist::class);
    }
}
