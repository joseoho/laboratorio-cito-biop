<?php

// namespace App\Http\Requests;

// use Illuminate\Foundation\Http\FormRequest;

// class StoreCitologiaRequest extends FormRequest
// {
//     /**
//      * Determine if the user is authorized to make this request.
//      */
//     public function authorize(): bool
//     {
//         return true;
//     }

//     /**
//      * Get the validation rules that apply to the request.
//      *
//      * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
//      */
//     public function rules(): array
//     {
//         return [
//           'id' => 'required|exists:pacientes,id',
//             // 'NB' => 'required_if:tipo_procedimiento,biopsia', // Solo requerido para biopsias
//             // 'NC' => 'required_if:tipo_procedimiento,citologia', // Solo requerido para citologías

//         ];
//     }
// }


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCitologiaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Cambia a true para permitir la validación
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            // Información del paciente (obligatoria)
            'IdPaciente' => 'required|integer|exists:pacientes,id',
            
            // Fechas
            'FechaRecibido' => 'nullable|date|date_format:Y-m-d',
            'FechaEnvio' => 'nullable|date|date_format:Y-m-d|after_or_equal:FechaRecibido',
            
            // Material y diagnóstico clínico
            'MaterialRemitido' => 'nullable|string|max:500',
            'DiagnosticoClinico' => 'nullable|string|max:1000',
            
            // Descripciones
            'Calidad' => 'nullable|string|max:2000',
            'DescripcionMacroscopica' => 'nullable|string|max:2000',
            'DescripcionCitologica' => 'nullable|string|max:3000',
            'DiagnosticoCitologico' => 'nullable|string|max:2000',
            'Notas' => 'nullable|string|max:2000',
            
            // Médicos
            'MedicoPatologo1' => 'nullable|string|max:255',
            'MedicoPatologo2' => 'nullable|string|max:255',
            
            // Índice hormonal
            'Superficiales' => 'nullable|integer|min:0|max:1000',
            'Intermedias' => 'nullable|integer|min:0|max:1000',
            'Parabasales' => 'nullable|integer|min:0|max:1000',
            'Conclusion' => 'nullable|string|max:2000',
        ];

        return $rules;
    }

    /**
     * Mensajes personalizados para las validaciones.
     */
    public function messages(): array
    {
        return [
            // IdPaciente
            'IdPaciente.required' => 'El ID del paciente es obligatorio.',
            'IdPaciente.integer' => 'El ID del paciente debe ser un número entero.',
            'IdPaciente.exists' => 'El paciente seleccionado no existe en la base de datos.',
            
            // Fechas
            'FechaRecibido.date' => 'La fecha de recepción debe ser una fecha válida.',
            'FechaRecibido.date_format' => 'El formato de la fecha de recepción debe ser YYYY-MM-DD.',
            'FechaEnvio.date' => 'La fecha de envío debe ser una fecha válida.',
            'FechaEnvio.date_format' => 'El formato de la fecha de envío debe ser YYYY-MM-DD.',
            'FechaEnvio.after_or_equal' => 'La fecha de envío debe ser igual o posterior a la fecha de recepción.',
            
            // Validaciones de longitud
            'MaterialRemitido.max' => 'El material remitido no debe exceder los 500 caracteres.',
            'DiagnosticoClinico.max' => 'El diagnóstico clínico no debe exceder los 1000 caracteres.',
            'Calidad.max' => 'La calidad de la muestra no debe exceder los 2000 caracteres.',
            'DescripcionMacroscopica.max' => 'La descripción macroscópica no debe exceder los 2000 caracteres.',
            'DescripcionCitologica.max' => 'La descripción citológica no debe exceder los 3000 caracteres.',
            'DiagnosticoCitologico.max' => 'El diagnóstico citológico no debe exceder los 2000 caracteres.',
            'Notas.max' => 'Las notas no deben exceder los 2000 caracteres.',
            'MedicoPatologo1.max' => 'El nombre del médico patólogo 1 no debe exceder los 255 caracteres.',
            'MedicoPatologo2.max' => 'El nombre del médico patólogo 2 no debe exceder los 255 caracteres.',
            
            // Índice hormonal
            'Superficiales.integer' => 'Las células superficiales deben ser un número entero.',
            'Superficiales.min' => 'Las células superficiales no pueden ser menores a 0.',
            'Superficiales.max' => 'Las células superficiales no pueden exceder 1000.',
            'Intermedias.integer' => 'Las células intermedias deben ser un número entero.',
            'Intermedias.min' => 'Las células intermedias no pueden ser menores a 0.',
            'Intermedias.max' => 'Las células intermedias no pueden exceder 1000.',
            'Parabasales.integer' => 'Las células parabasales deben ser un número entero.',
            'Parabasales.min' => 'Las células parabasales no pueden ser menores a 0.',
            'Parabasales.max' => 'Las células parabasales no pueden exceder 1000.',
            'Conclusion.max' => 'La conclusión no debe exceder los 2000 caracteres.',
        ];
    }

    /**
     * Atributos personalizados para los mensajes de error.
     */
    public function attributes(): array
    {
        return [
            'IdPaciente' => 'ID del paciente',
            'FechaRecibido' => 'fecha de recepción',
            'FechaEnvio' => 'fecha de envío',
            'MaterialRemitido' => 'material remitido',
            'DiagnosticoClinico' => 'diagnóstico clínico',
            'Calidad' => 'calidad de la muestra',
            'DescripcionMacroscopica' => 'descripción macroscópica',
            'DescripcionCitologica' => 'descripción citológica',
            'DiagnosticoCitologico' => 'diagnóstico citológico',
            'Notas' => 'notas',
            'MedicoPatologo1' => 'médico patólogo 1',
            'MedicoPatologo2' => 'médico patólogo 2',
            'Superficiales' => 'células superficiales',
            'Intermedias' => 'células intermedias',
            'Parabasales' => 'células parabasales',
            'Conclusion' => 'conclusión',
        ];
    }

    /**
     * Preparar los datos para la validación.
     */
    protected function prepareForValidation()
    {
        // Convertir campos vacíos a null
        $this->merge([
            'FechaRecibido' => $this->FechaRecibido ?: null,
            'FechaEnvio' => $this->FechaEnvio ?: null,
            'MaterialRemitido' => $this->MaterialRemitido ? trim($this->MaterialRemitido) : null,
            'DiagnosticoClinico' => $this->DiagnosticoClinico ? trim($this->DiagnosticoClinico) : null,
            'Calidad' => $this->Calidad ? trim($this->Calidad) : null,
            'DescripcionMacroscopica' => $this->DescripcionMacroscopica ? trim($this->DescripcionMacroscopica) : null,
            'DescripcionCitologica' => $this->DescripcionCitologica ? trim($this->DescripcionCitologica) : null,
            'DiagnosticoCitologico' => $this->DiagnosticoCitologico ? trim($this->DiagnosticoCitologico) : null,
            'Notas' => $this->Notas ? trim($this->Notas) : null,
            'MedicoPatologo1' => $this->MedicoPatologo1 ? trim($this->MedicoPatologo1) : null,
            'MedicoPatologo2' => $this->MedicoPatologo2 ? trim($this->MedicoPatologo2) : null,
            'Superficiales' => $this->Superficiales ? (int) $this->Superficiales : null,
            'Intermedias' => $this->Intermedias ? (int) $this->Intermedias : null,
            'Parabasales' => $this->Parabasales ? (int) $this->Parabasales : null,
            'Conclusion' => $this->Conclusion ? trim($this->Conclusion) : null,
        ]);
    }
}