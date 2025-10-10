<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiagCito extends Model
{
      use HasFactory;
    // Protected $primaryKey = "id";
    protected $table ='diagcito';
    protected $fillable = [
        'Cod',
        'Descripcion',
        'Diagnostico',
        'Orden',
      
    ];
}
