<?php

namespace Tests\Unit;

use App\Enums\CaseType;
use App\Services\CaseNumberGenerator;
use Tests\TestCase;

class CaseNumberGeneratorTest extends TestCase
{
    public function test_formats_biopsy_as_four_digits_plus_year(): void
    {
        $generator = new CaseNumberGenerator();

        $this->assertSame('083526', $generator->format(CaseType::Biopsy, 835, 2026));
    }

    public function test_formats_cytology_as_five_digits_plus_year(): void
    {
        $generator = new CaseNumberGenerator();

        $this->assertSame('0360626', $generator->format(CaseType::Cytology, 3606, 2026));
    }

    public function test_parses_legacy_biopsy_number(): void
    {
        $generator = new CaseNumberGenerator();

        $this->assertSame([133, 2016], $generator->parse(CaseType::Biopsy, '013316'));
    }

    public function test_parses_legacy_cytology_number(): void
    {
        $generator = new CaseNumberGenerator();

        $this->assertSame([4312, 2012], $generator->parse(CaseType::Cytology, '0431212'));
    }

    public function test_rejects_wrong_length(): void
    {
        $generator = new CaseNumberGenerator();

        $this->assertNull($generator->parse(CaseType::Biopsy, '12345'));
        $this->assertNull($generator->parse(CaseType::Cytology, '123456'));
    }

    public function test_rejects_non_numeric(): void
    {
        $generator = new CaseNumberGenerator();

        $this->assertNull($generator->parse(CaseType::Biopsy, 'B-1234'));
    }

    public function test_round_trip(): void
    {
        $generator = new CaseNumberGenerator();
        $code = $generator->format(CaseType::Cytology, 137, 2026);

        $this->assertSame([137, 2026], $generator->parse(CaseType::Cytology, $code));
    }
}
