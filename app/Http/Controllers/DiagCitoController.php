<?php

namespace App\Http\Controllers;

use App\Models\DiagCito;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDiagCitoRequest;
use App\Http\Requests\UpdateDiagCitoRequest;

class DiagCitoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DiagCito::query();
        
        // Búsqueda solo por Código
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('Cod', 'LIKE', "%{$search}%");
        }
        
        // Ordenar por el campo Orden (asumiendo que quieres un orden específico)
        $query->orderBy('Orden', 'asc');
        
        $diagcitos = $query->paginate(10);
        
        return view('diagcito.index', compact('diagcitos'));
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
    public function store(StoreDiagCitoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(DiagCito $diagCito)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DiagCito $diagCito)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiagCitoRequest $request, DiagCito $diagCito)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DiagCito $diagCito)
    {
        //
    }
}
