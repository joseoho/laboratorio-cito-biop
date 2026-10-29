<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pathologist extends Model
{
    protected $table = 'pathologists';

    public $timestamps = false;

    protected $fillable = ['name'];
}
