<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePacienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pacienteId = $this->route('pacientes') ? $this->route('pacientes')->id : null;
        return [
        // 'id' => 'required|exists:pacientes,id',
                'NB' => [
                'nullable',
                'numeric',
                'min:1',
                'max:9999999999', // 10 dígitos máximo
                Rule::unique('pacientes')->ignore($pacienteId),
                function ($attribute, $value, $fail) {
                    // Validar que NB y NC no estén ambos presentes
                    if ($value && $this->NC) {
                        $fail('El Paciente solo se puede asignar número de Biopsia (NB) ó número de Citología (NC). ambos NO.');
                    }
                    
                    // Validar que tenga máximo 10 caracteres numéricos
                    if ($value && strlen((string)$value) > 10) {
                        $fail('El número de Biopsia (NB) no puede tener más de 10 dígitos.');
                    }
                },
            ],
            'NC' => [
                'nullable',
                'numeric',
                'min:1',
                'max:9999999999', // 10 dígitos máximo
                Rule::unique('pacientes')->ignore($pacienteId),
                function ($attribute, $value, $fail) {
                    // Validar que NB y NC no estén ambos presentes
                    if ($value && $this->NB) {
                        $fail('El Paciente solo se puede asignar número de Citología (NC) ó número de Biopsia (NB). ambos NO.');
                    }
                    
                    // Validar que tenga máximo 10 caracteres numéricos
                    if ($value && strlen((string)$value) > 10) {
                        $fail('El número de Citología (NC) no puede tener más de 10 dígitos.');
                    }
                },
            ],
            // 'status' => 'sometimes|in:0,1'
        
        // 'NB' => 'required|numeric|digits:7|unique:pacientes,NB',
        // 'NC' => 'required|numeric|digits:7|unique:pacientes,NC',
        // 'NB' => 'required_without:NC|numeric|digits:7|unique:pacientes,NB',
        // 'NC' => 'required_without:NB|numeric|digits:7|unique:pacientes,NC',
    ];
    }

    public function messages()
    {
        return [
            // 'NB.required_without' => 'El número de biopsia (NB) es requerido si el número de citología (NC) no se proporciona.',
            // 'NC.required_without' => 'El número de citología (NC) es requerido si el número de biopsia (NB) no se proporciona.',
            'NB.numeric' => 'El número de biopsia (NB) debe ser un número.',
            'NC.numeric' => 'El número de citología (NC) debe ser un número.',
            'NB.digits' => 'El número de biopsia (NB) debe tener 7 dígitos.',
            'NC.digits' => 'El número de citología (NC) debe tener 7 dígitos.',
            'NB.unique' => 'El número de biopsia (NB) ya está en uso.',
            'NC.unique' => 'El número de citología (NC) ya está en uso.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'NB' => $this->NB ? (int)$this->NB : null,
            'NC' => $this->NC ? (int)$this->NC : null,
        ]);
    }
}
