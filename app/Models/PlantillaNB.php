<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantillaNB extends Model
{
    use HasFactory;
    // Protected $primaryKey = "id";
    protected $table ='plantillas';
    protected $fillable = [
        'modulo',
        'nombre',
        'contenido',
      
    ];
}
