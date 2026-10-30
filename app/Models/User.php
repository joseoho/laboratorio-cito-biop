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
    public const ROLE_OPERADOR = 'operador';

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

    public function isOperator(): bool
    {
        return $this->role === self::ROLE_OPERADOR;
    }

    /** El operador hace todo el trabajo diario; el administrador además reabre, depura y respalda. */
    public function canManagePatients(): bool
    {
        return $this->isAdmin() || $this->isOperator();
    }

    public function canEditReports(): bool
    {
        return $this->isAdmin() || $this->isOperator();
    }

    public function canViewReports(): bool
    {
        return $this->isAdmin() || $this->isOperator();
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_OPERADOR => 'Operador',
            default => (string) $this->role,
        };
    }
}
