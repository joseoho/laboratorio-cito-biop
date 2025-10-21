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
    public function index(Request $request)
    {
         $query = Biopsia::with('paciente')->orderBy('created_at', 'desc');
    
    // Búsqueda por número de citología (NC del paciente)
    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->whereHas('paciente', function($q) use ($search) {
            $q->where('NB', 'LIKE', "%{$search}%")
            //   ->orWhere('Nombre', 'LIKE', "%{$search}%")
              ->orWhere('CI', 'LIKE', "%{$search}%");
        });
    }
    
    $biopsias = $query->paginate(10);
    
    return view('biopsia.index', compact('biopsias'));
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
    public function show(string $id)
    {
       // Buscar por IdPaciente en lugar de id
    $biopsia = Biopsia::with('paciente')
        ->where('Id', $id)
        ->firstOrFail();
    
    return view('biopsia.show', compact('biopsia'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // El $id que recibimos es el NC (número de citología) del paciente
    // Buscar el paciente por su número de citología (NC), que es único, este el el metodo correcto
    // para crear citologia con datos del paciente
    $paciente = Paciente::where('id', $id)->first();
    
    if (!$paciente) {
        return redirect()->route('pacientes.index')
            ->with('error', 'No se encontró el paciente con número de biopsia: ' . $id);
    }

    // Buscar si ya existe una citología para este paciente
    // $anexos = Anexo::all(); 
    // $diagcitos = DiagCito::all(); 
    $biopsia = Biopsia::where('id', $id)->first();

    return view('biopsia.biopsia', compact('paciente', 'biopsia'));//, 'anexos', 'diagcitos'));
 
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
