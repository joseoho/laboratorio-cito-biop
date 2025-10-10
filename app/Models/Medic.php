<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medic extends Model
{
    use HasFactory;
    //    Protected $primaryKey = "id";
       protected $table ='medico';
       protected $fillable = [
           'Codigo',
           'Nombre',
           'Campo1',
           'Telefono',
         
       ];

       public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'Dr');
    }
}