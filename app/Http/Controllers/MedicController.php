<?php

namespace App\Http\Controllers;

use App\Models\Medic;   
use Illuminate\Http\Request;
use App\Http\Requests\StoreMedicRequest;
use App\Http\Requests\UpdateMedicRequest;

class MedicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
            $query = Medic::query();
        
            // Búsqueda solo por código
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('codigo', 'like', "%{$search}%");
            }

            $medicos = $query->orderBy('nombre')->paginate(15);

            return view('medico.index', compact('medicos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('medico.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicRequest $request)
    {
            
            try {
        Medic::create([
            'Codigo' => $request->codigo,
            'Nombre' => $request->nombre,
            // 'especialidad' => $request->especialidad,
            'Telefono' => $request->telefono,
            // 'email' => $request->email,
            // 'observaciones' => $request->observaciones,
            // 'activo' => $request->activo ?? 1 // Valor por defecto activo
        ]);
        
        return redirect()->route('medicos.index')
            ->with('success', 'Médico creado exitosamente.');
            
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error al crear el médico: ' . $e->getMessage())
            ->withInput();
    }
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Medic $medic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Medic $medic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicRequest $request, Medic $medic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medic $medic)
    {
       
            Medic::find($medic)->delete();
            
            return redirect()->route('medico.index')
                ->with('success', 'Médico eliminado exitosamente.');
    }

     public function getMedico($id)
    {
        $medico = Medic::find($id);
        
        if ($medico) {
            return response()->json([
                'success' => true,
                'medico' => $medico
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Médico no encontrado'
        ], 404);
    }
}
