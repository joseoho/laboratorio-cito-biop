<?php

namespace App\Enums;

enum CaseStatus: string
{
    case Received = 'received';
    case InProgress = 'in_progress';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Received => 'Recibido',
            self::InProgress => 'En proceso',
            self::Delivered => 'Entregado',
            self::Cancelled => 'Anulado',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Received => 'secondary',
            self::InProgress => 'warning',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };
    }
}
