<?php

namespace App\Http\Controllers;

use App\Models\Anexo;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAnexoRequest;
use App\Http\Requests\UpdateAnexoRequest;

class AnexoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
             $query = Anexo::query();
        
        // Búsqueda solo por código
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('Cod', 'LIKE', "%{$search}%");
        }
        
        // Ordenar por orden por defecto
        $query->orderBy('Orden', 'asc');
        
        $anexos = $query->paginate(10);
        
        return view('anexo.index', compact('anexos'));
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
    public function store(StoreAnexoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Anexo $anexo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Anexo $anexo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnexoRequest $request, Anexo $anexo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Anexo $anexo)
    {
        //
    }
}
