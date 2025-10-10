<?php

namespace App\Http\Controllers;

use App\Models\Citologia;
use App\Models\Paciente;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCitologiaRequest;
use App\Http\Requests\UpdateCitologiaRequest;

class CitologiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
       
    
    
}
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCitologiaRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $paciente = Paciente::with(['citologias'])->findOrFail($paciente->id);
        return view('citologia.citologia', compact('paciente'));
    }

 
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
 // El $id que recibimos es el NC (número de citología) del paciente
    // Buscar el paciente por su número de citología (NC)
    $paciente = Paciente::where('NC', $id)->first();
    
    if (!$paciente) {
        return redirect()->route('pacientes.index')
            ->with('error', 'No se encontró el paciente con número de citología: ' . $id);
    }

    // Buscar si ya existe una citología para este paciente
    $citologia = Citologia::where('IdPaciente', $id)->first();

    return view('citologia.citologia', compact('paciente', 'citologia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCitologiaRequest $request, Citologia $citologia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Citologia $citologia)
    {
        //
    }

   
}
