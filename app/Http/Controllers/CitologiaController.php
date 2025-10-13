<?php

namespace App\Http\Controllers;

use App\Models\Citologia;
use App\Models\Paciente;
use App\Models\Patologo;        
use App\Models\DiagCito;
use App\Models\Anexo;
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
        
                try {
            $citologia = new Citologia();
            
            // Datos básicos del paciente (ya vienen en la vista)
            $citologia->paciente_id = $request->id;
            $citologia->CI = $request->ci;
            $citologia->NC = $request->nc;
            $citologia->Nombre = $request->nombre;
            
            // Identificación de la muestra
            $citologia->FechaRecibido = $request->FechaRecibido;
            $citologia->FechaEnvio = $request->FechaEnvio;
            
            // Material y diagnóstico
            $citologia->MaterialRemitido = $request->MaterialRemitido;
            $citologia->DiagnosticoClinico = $request->DiagnosticoClinico;
            
            // Calidad y descripción
            $citologia->calidad_muestra = $request->calidad_muestra;
            $citologia->DescripcionMacroscopica = $request->DescripcionMacroscopica;
            
            // Descripción citología
            $citologia->codigo = $request->codigo;
            $citologia->anexo = $request->anexo;
            $citologia->descripcion_citologia = $request->descripcion_citologia;
            $citologia->diagnostico_citologia = $request->diagnostico_citologia;
            $citologia->notas = $request->notas;
            
            // Índice de valoración hormonal
            $citologia->superficiales = $request->superficiales;
            $citologia->intermedias = $request->intermedias;
            $citologia->parabasales = $request->parabasales;
            
            // Médicos patólogos
            $citologia->medico_patologo_1 = $request->medico_patologo_1;
            $citologia->medico_patologo_2 = $request->medico_patologo_2;
            
            $citologia->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Citología guardada correctamente',
                'data' => $citologia
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la citología: ' . $e->getMessage()
            ], 500);
        }
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
