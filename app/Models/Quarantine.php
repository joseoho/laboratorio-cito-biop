<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quarantine extends Model
{
    protected $table = 'quarantine';

    public $timestamps = false;

    protected $fillable = [
        'bucket', 'legacy_table', 'legacy_row_id', 'legacy_key', 'detail', 'payload',
        'status', 'reviewed_by', 'reviewed_at', 'note',
    ];

    protected $casts = [
        'payload' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeBucket(Builder $query, ?string $bucket): Builder
    {
        return $bucket ? $query->where('bucket', $bucket) : $query;
    }
}
