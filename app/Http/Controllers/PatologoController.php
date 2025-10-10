<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;    
use Illuminate\Http\Request;
use App\Models\Patologo;
use App\Http\Requests\StorePatologosRequest;
use App\Http\Requests\UpdatePatologosRequest;

class PatologoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
            // Query base
        $query = Patologo::query();
        
        // Búsqueda por nombre
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('Patologo', 'LIKE', "%{$search}%");
        }
        
        // Ordenar por ID descendente (los más nuevos primero)
        $query->orderBy('Patologo', 'desc');
        
        // Paginación (15 registros por página)
        $patologos = $query->paginate(15);
        
        // Si hay búsqueda, mantener los parámetros en la paginación
        if ($request->has('search')) {
            $patologos->appends(['search' => $request->search]);
        }
        
        return view('patologo.index', compact('patologos'));
      }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePatologosRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePatologosRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Patologos  $patologos
     * @return \Illuminate\Http\Response
     */
    public function show(Patologos $patologos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Patologos  $patologos
     * @return \Illuminate\Http\Response
     */
    public function edit(Patologos $patologos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePatologosRequest  $request
     * @param  \App\Models\Patologos  $patologos
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePatologosRequest $request, Patologos $patologos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Patologos  $patologos
     * @return \Illuminate\Http\Response
     */
    public function destroy(Patologos $patologos)
    {
        //
    }
}