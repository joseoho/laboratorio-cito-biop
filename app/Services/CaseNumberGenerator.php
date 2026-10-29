<?php

namespace App\Services;

use App\Enums\CaseType;
use App\Models\ClinicalCase;

/**
 * Numeración del laboratorio: secuencia + año (2 dígitos).
 *   Biopsia   -> NNNNYY  (ej. 083526 = biopsia 835 del 2026)
 *   Citología -> NNNNNYY (ej. 0360626 = citología 3606 del 2026)
 *
 * No renumera nada: solo calcula el siguiente número libre del año.
 */
class CaseNumberGenerator
{
    public function width(CaseType $type): int
    {
        return $type === CaseType::Biopsy ? 4 : 5;
    }

    public function format(CaseType $type, int $sequence, int $year): string
    {
        return str_pad((string) $sequence, $this->width($type), '0', STR_PAD_LEFT)
            . str_pad((string) ($year % 100), 2, '0', STR_PAD_LEFT);
    }

    public function next(CaseType $type, ?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        $max = ClinicalCase::query()
            ->where('type', $type)
            ->where('year_no', $year)
            ->max('sequence_no');

        return $this->format($type, ((int) $max) + 1, $year);
    }

    /**
     * Descompone un número en [secuencia, año]. Null si no respeta el formato.
     *
     * @return array{0:int,1:int}|null
     */
    public function parse(CaseType $type, string $code): ?array
    {
        $code = trim($code);
        $width = $this->width($type);

        if (! preg_match('/^\d{' . ($width + 2) . '}$/', $code)) {
            return null;
        }

        $sequence = (int) substr($code, 0, $width);
        $year = 2000 + (int) substr($code, -2);

        return [$sequence, $year];
    }
}
