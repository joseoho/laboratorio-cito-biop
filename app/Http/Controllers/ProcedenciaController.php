<?php

namespace App\Http\Controllers;

use App\Models\Procedencia;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProcedenciaRequest;
use App\Http\Requests\UpdateProcedenciaRequest;

class ProcedenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         $query = Procedencia::query();
        
        // Búsqueda solo por Código
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('Codigo', 'like', "%{$search}%");
        }
        
        $procedencias = $query->paginate(10);
        
        return view('procedencia.index', compact('procedencias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('procedencia.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProcedenciaRequest $request)
    {
           try {
            Procedencia::create($request->all());
            
            return redirect()->route('procedencias.index')
                ->with('success', 'Procedencia creada exitosamente.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la procedencia: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Procedencia $procedencia)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Procedencia $procedencia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProcedenciaRequest $request, Procedencia $procedencia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Procedencia $codigo)
    {
       Procedencia::find($codigo)->delete();
            
            return redirect()->route('procedencia.index')
                ->with('success', 'Médico eliminado exitosamente.');
    }
}
