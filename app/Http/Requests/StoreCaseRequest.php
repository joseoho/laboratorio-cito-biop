<?php

namespace App\Http\Requests;

use App\Enums\CaseType;
use App\Models\ClinicalCase;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = CaseType::tryFrom((string) $this->input('type')) ?? CaseType::Biopsy;
        $width = $type === CaseType::Biopsy ? 4 : 5;

        return [
            'type' => ['required', Rule::enum(CaseType::class)],
            'case_code' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($type, $width) {
                    $code = trim((string) $value);

                    if (! preg_match('/^\d{' . ($width + 2) . '}$/', $code)) {
                        $fail(sprintf(
                            'El número de %s debe tener %d dígitos (secuencia de %d + año de 2).',
                            $type->label(),
                            $width + 2,
                            $width
                        ));

                        return;
                    }

                    $exists = ClinicalCase::query()
                        ->where('type', $type)
                        ->where('case_code', $code)
                        ->exists();

                    if ($exists) {
                        $fail('Ese número de ' . $type->label() . ' ya está registrado.');
                    }
                },
            ],
            'received_at' => ['nullable', 'date', 'before_or_equal:today'],
            'material' => ['nullable', 'string', 'max:' . ($type === CaseType::Biopsy ? 100 : 250)],
            'clinical_diagnosis' => ['nullable', 'string', 'max:' . ($type === CaseType::Biopsy ? 100 : 60)],
        ];
    }

    public function messages(): array
    {
        return [
            'received_at.before_or_equal' => 'La fecha de recepción no puede ser futura.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'case_code' => trim((string) $this->input('case_code')),
            'material' => $this->filled('material') ? trim((string) $this->input('material')) : null,
            'clinical_diagnosis' => $this->filled('clinical_diagnosis') ? trim((string) $this->input('clinical_diagnosis')) : null,
        ]);
    }
}
