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
    public function index(Request $request)
    {
             $query = Citologia::with('paciente')->orderBy('created_at', 'desc');
    
    // Búsqueda por número de citología (NC del paciente)
    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->whereHas('paciente', function($q) use ($search) {
            $q->where('NC', 'LIKE', "%{$search}%")
            //   ->orWhere('Nombre', 'LIKE', "%{$search}%")
              ->orWhere('CI', 'LIKE', "%{$search}%");
        });
    }
    
    $citologias = $query->paginate(10);
    
    return view('citologia.index', compact('citologias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
    
         return view('citologia.citologia');
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCitologiaRequest $request)
    {
        
    try {
            $pacienteId = $request->id_paciente ?? $request->paciente_id;
        
        if (!$pacienteId) {
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ No se recibió el ID del paciente');
        }
            
            $citologia = Citologia::create($request->all());
              // ACTUALIZAR STATUS DEL PACIENTE A 1
        if ($citologia->IdPaciente) {
            $paciente = Paciente::find($citologia->IdPaciente);
            if ($paciente) {
                $paciente->status = 1;
                $paciente->save();
            }
        }
            return redirect()->route('citologias.index')
                ->with('success', 'Paciente guardado correctamente!');
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
    public function show(string $id)
{
    // Buscar por IdPaciente en lugar de id
    $citologia = Citologia::with('paciente')
        ->where('IdPaciente', $id)
        ->firstOrFail();
    
    return view('citologia.show', compact('citologia'));
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
            ->with('error', 'No se encontró el paciente con número de citología: ' . $id);
    }

    // Buscar si ya existe una citología para este paciente
    $anexos = Anexo::all(); 
    $diagcitos = DiagCito::all(); 
    $citologia = Citologia::where('IdPaciente', $id)->first();

    return view('citologia.citologia', compact('paciente', 'citologia', 'anexos', 'diagcitos'));
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

    public function imprimir($id)
    {
         $citologia = Citologia::with('paciente')
        ->where('IdPaciente', $id)
        ->firstOrFail();
         return view('citologia.imprimir', compact('citologia'));
    }

    public function vistaPrevia($id)
    {
        $citologia = Citologia::with('paciente')->findOrFail($id);
        return view('citologias.vista-previa', compact('citologia'));
    }
   
}
