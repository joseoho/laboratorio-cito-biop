<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;
    
    protected $primaryKey = "id";
    protected $table = 'pacientes';
    
    protected $fillable = [
        'id',
        'FechaApertura',
        'CI',
        'Nombre',
        'Sexo',
        'Dirección',
        'Teléfono',
        'Edad',
        'Procedencia',  // Esta columna probablemente contiene el ID de la procedencia
        'Dr',           // Esta columna probablemente contiene el ID del médico
        'mail',
        'NB',
        'NC',
        'SMS',
        'SMSStatus',
        'FechaComp',
        'status',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date'
    ];

    /**
     * Relación con el modelo Medico
     * 'Dr' es la columna en pacientes que contiene el ID del médico
     * 'Codigo' es la columna en medicos que es la clave primaria
     */
    public function medico()
    {
        return $this->belongsTo(Medic::class, 'Dr', 'Codigo');
    }

    /**
     * Relación con el modelo Procedencia
     * 'Procedencia' es la columna en pacientes que contiene el ID de la procedencia
     * 'Codigo' es la columna en procedencias que es la clave primaria
     */
    public function procedencia()
    {
        return $this->belongsTo(Procedencia::class, 'Procedencia', 'Codigo');
    }

    /**
     * Relación: Un paciente tiene MUCHAS biopsias (si necesitas múltiples)
     */
    public function biopsias()
    {
        return $this->hasMany(Biopsia::class, 'id', 'NB');
    }

    /**
     * Relación: Un paciente tiene MUCHAS citologías (si necesitas múltiples)
     */
    public function citologias()
{
     return $this->hasMany(Citologia::class, 'IdPaciente', 'id');
}
}