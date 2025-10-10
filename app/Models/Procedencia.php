<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedencia extends Model
{
    use HasFactory;
       // Protected $primaryKey = "id";
       protected $table ='procedencia';
       protected $fillable = [
           'Codigo',
           'ListaPrecio',
         
         
       ];
}
