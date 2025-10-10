<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Inicio extends Controller
{
    public function index()
    {

        // return view ('inicio', compact('ingresos'));
        return view('inicio');
    }
}
