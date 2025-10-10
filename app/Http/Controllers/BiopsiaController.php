<?php

namespace App\Http\Controllers;

use App\Models\Biopsia;
use App\Models\Paciente;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBiopsiaRequest;
use App\Http\Requests\UpdateBiopsiaRequest;

class BiopsiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $pacientes = DB::table('pacientes')
            ->select('FechaApertura', 'CI', 'Nombre', 'Sexo', 'Dirección', 'CodTelf', 'Teléfono', 'Edad', 'Procedencia', 'Dr', 'NB','SMSStatus','FechaComp')
            ->whereNotNull('NB')
            ->get();
        return view('biopsia.biopsia', compact('pacientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBiopsiaRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Biopsia $biopsia)
    {
        $paciente = Paciente::where('NB', $biopsia)->first();
        
        if (!$paciente) {
            return redirect()->route('biopsia.form')
                ->with('error', 'No se encontró paciente con NB-' . $biopsia);
        }

        // Buscar si ya existe una biopsia para este paciente
        $biopsia = Biopsia::where('paciente_id', $paciente->id)
                         ->where('NB', $biopsia)
                         ->first();

        return view('biopsia.biopsia', [
            'paciente' => $paciente,
            'numero_biopsia' => $biopsia,
            'biopsia' => $biopsia // Pasar datos existentes si hay
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Biopsia $biopsia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBiopsiaRequest $request, Biopsia $biopsia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Biopsia $biopsia)
    {
        //
    }
}
