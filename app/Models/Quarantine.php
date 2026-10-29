<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quarantine extends Model
{
    protected $table = 'quarantine';

    public $timestamps = false;

    protected $fillable = ['bucket', 'legacy_table', 'legacy_row_id', 'legacy_key', 'detail', 'payload'];

    protected $casts = [
        'payload' => 'array',
    ];
}
