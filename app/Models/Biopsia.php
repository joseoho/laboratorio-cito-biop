<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biopsia extends Model
{
    use HasFactory;
    Protected $primaryKey = "id";
    protected $table ='biopsias';
    protected $fillable = [
        'FechaRecibido',
        'FechaEnvio',
        'MaterialRemitido',
        'DiagnosticoClinico',
        'SitioLesion',
        'Descripción Macroscópica',
        'Descripción Microscópica',
        'Diagnóstico Microscópico',
        'Notas',
        'Medico Patólogo 1',
        'Medico Patólogo 2',
        'Foto 1',
        'Foto 2',
        'Foto 3',
        'Com 1',
        'Com 2',
        'Com 3',
        
    ];

    /**
     * Relación: Una biopsia pertenece a UN paciente
     * Se relaciona por el ID de la biopsia con el campo NB del paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'id', 'id');
    }
}