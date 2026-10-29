<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Origin extends Model
{
    protected $table = 'origins';

    public $timestamps = false;

    protected $fillable = ['legacy_code', 'price_list'];
}
