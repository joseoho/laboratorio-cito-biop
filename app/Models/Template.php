<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'templates';

    public $timestamps = false;

    protected $fillable = ['module', 'name', 'content'];

    public const MODULE_MACRO = '00';
    public const MODULE_MICRO = '01';
    public const MODULE_DIAGNOSIS = '02';
}
