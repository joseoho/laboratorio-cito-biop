<?php

namespace App\Http\Controllers;

use App\Models\PlantillaNB;
use Illuminate\Http\Request;
use App\Http\Requests\StorePlantillaNBRequest;
use App\Http\Requests\UpdatePlantillaNBRequest;

class PlantillaNBController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $plantillas = PlantillaNB::when($search, function ($query, $search) {
                return $query->where('nombre', 'LIKE', "%{$search}%");
            })
            ->orderBy('modulo')
            ->orderBy('nombre')
            ->paginate(10);

        return view('plantillanb.index', compact('plantillas', 'search'));
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
    public function store(StorePlantillaNBRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PlantillaNB $plantillaNB)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlantillaNB $plantillaNB)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlantillaNBRequest $request, PlantillaNB $plantillaNB)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlantillaNB $plantillaNB)
    {
        //
    }
}
