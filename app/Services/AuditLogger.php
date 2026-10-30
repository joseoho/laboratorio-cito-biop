<?php

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;

/**
 * Registro de auditoría clínica: quién hizo qué, sobre qué registro y cuándo.
 * Append-only: nunca se actualiza ni se borra un evento.
 */
class AuditLogger
{
    public function log(string $action, Model $subject, array $changes = []): AuditEvent
    {
        return AuditEvent::create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'changes' => $changes !== [] ? $changes : null,
            'ip' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}
