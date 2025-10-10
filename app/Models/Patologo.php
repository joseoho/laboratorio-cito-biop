<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patologo extends Model
{
    use HasFactory;
    // Protected $primaryKey = "id";
    protected $table ='patologo';
    protected $fillable = [
        'Patologo',
    ];
}
