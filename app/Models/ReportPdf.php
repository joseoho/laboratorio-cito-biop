<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportPdf extends Model
{
    protected $table = 'report_pdfs';

    public $timestamps = false;

    protected $fillable = ['case_id', 'path', 'hash', 'version', 'generated_by', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
        'version' => 'integer',
    ];

    public function clinicalCase(): BelongsTo
    {
        return $this->belongsTo(ClinicalCase::class, 'case_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
