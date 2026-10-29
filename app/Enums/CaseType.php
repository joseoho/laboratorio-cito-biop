<?php

namespace App\Enums;

enum CaseType: string
{
    case Biopsy = 'biopsy';
    case Cytology = 'cytology';

    public function label(): string
    {
        return match ($this) {
            self::Biopsy => 'Biopsia',
            self::Cytology => 'Citología',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::Biopsy => 'B',
            self::Cytology => 'C',
        };
    }
}
