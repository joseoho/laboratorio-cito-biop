<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CytologyDiagnosis extends Model
{
    protected $table = 'cytology_diagnosis_catalog';

    public $timestamps = false;

    protected $fillable = ['code', 'description', 'diagnosis', 'sort_order'];
}
