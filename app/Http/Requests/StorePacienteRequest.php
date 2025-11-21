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
        // $pacienteId = $this->route('pacientes') ? $this->route('pacientes')->id : null;
        return 
        [
            // 'NB' => 'nullable|numeric|unique:pacientes,NB|required_without:NC',
            // 'NC' => 'nullable|numeric|unique:pacientes,NC|required_without:NB',
            'mail' => 'required|email|max:100',
            'NB' => [
                'nullable',
                'numeric',
                Rule::unique('pacientes', 'NB'),
            ],
            'NC' => [
                'nullable', 
                'numeric',
                Rule::unique('pacientes', 'NC'),
            ],
        ];
        
    }

        public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();
            
            \Log::info('Validando NB y NC:', [
                'NB' => $data['NB'] ?? null,
                'NC' => $data['NC'] ?? null
            ]);

            // Validar que al menos uno de los dos esté presente
            if (empty($data['NB']) && empty($data['NC'])) {
                $validator->errors()->add('NB', 'Debe proporcionar al menos un número de Biopsia (NB) o Citología (NC).');
                \Log::warning('Validación fallida: Ambos campos NB y NC están vacíos');
            }
            
            // Validar que no estén ambos presentes
            if (!empty($data['NB']) && !empty($data['NC'])) {
                $validator->errors()->add('NB', 'No puede tener tanto número de Biopsia (NB) como número de Citología (NC). Elija solo uno.');
                $validator->errors()->add('NC', 'No puede tener tanto número de Biopsia (NB) como número de Citología (NC). Elija solo uno.');
                \Log::warning('Validación fallida: Ambos campos NB y NC tienen valores');
            }
        });
    }
    public function messages()
    {
        
        return [
            'mail.required' => 'El correo electrónico es obligatorio.',
            'mail.email' => 'Debe ingresar un correo electrónico válido.',
            'mail.max' => 'El correo no puede tener más de 100 caracteres.',
            
            'NB.numeric' => 'El número de Biopsia debe ser numérico.',
            'NB.unique' => 'Este número de Biopsia ya está registrado.',
            
            'NC.numeric' => 'El número de Citología debe ser numérico.',
            'NC.unique' => 'Este número de Citología ya está registrado.',
        
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'NB' => $this->NB ? (int)$this->NB : null,
            'NC' => $this->NC ? (int)$this->NC : null,
        ]);

        \Log::info('Datos preparados para validación:', [
            'NB' => $this->NB,
            'NC' => $this->NC
        ]);
    }
}
