<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_RECEPCION = 'recepcion';
    public const ROLE_PATOLOGO = 'patologo';
    public const ROLE_AUDITOR = 'auditor';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class, 'actor_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function canManagePatients(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_RECEPCION], true);
    }

    public function canEditReports(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_PATOLOGO], true);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_RECEPCION => 'Recepción',
            self::ROLE_PATOLOGO => 'Patólogo',
            self::ROLE_AUDITOR => 'Auditor',
            default => (string) $this->role,
        };
    }
}
