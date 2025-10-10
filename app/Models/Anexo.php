<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anexo extends Model
{
    use HasFactory;
    // Protected $primaryKey = "id";
    protected $table ='anexo';
    protected $fillable = [
        'Cod',
        'Anexo',
        'Orden',
      
    ];
}