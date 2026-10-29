<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annex extends Model
{
    protected $table = 'annexes';

    public $timestamps = false;

    protected $fillable = ['code', 'text', 'sort_order'];
}
