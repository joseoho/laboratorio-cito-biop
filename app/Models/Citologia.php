<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Citologia extends Model
{
    use HasFactory;
    Protected $primaryKey ="IdPaciente";
    protected $table ='citologia';
    protected $fillable = [
        'IdPaciente',
        'FechaRecibido',
        'FechaEnvio',
        'MaterialRemitido',
        'DiagnosticoClinico',
        'Calidad',
        'DescripcionMacroscopica',
        'DescripcionCitologica',
        'DiagnosticoCitologico',
        'Notas',
        'MedicoPatologo1',
        'MedicoPatologo2',
        'Superficiales',
        'Intermedias',
        'Parabasales',
        'Conclusion',
        // 'Calidad',
    ];

      /**
     * Relación: Una citología pertenece a UN paciente
     * Se relaciona por el IdPaciente con el campo NC del paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'IdPaciente', 'id');
    }
}