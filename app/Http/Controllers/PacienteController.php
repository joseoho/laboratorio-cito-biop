<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Medic;
use App\Models\Procedencia;
use App\Models\Patologo;        
use App\Models\DiagCito;
use App\Models\Anexo;
use Illuminate\Http\Request;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        

      $query = Paciente::with(['procedencia', 'medico', 'biopsias', 'citologias']);
    
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->get('search');
        $tipo = $request->get('tipo', 'CI'); // Default buscar por CI/Nombre
        
        switch ($tipo) {
            case 'ci':
                $query->where(function($q) use ($search) {
                    $q->where('CI', 'LIKE', "%{$search}%")
                      ->orWhere('Nombre', 'LIKE', "%{$search}%");
                });
                break;
            case 'nb':
                $query->where('NB', 'LIKE', "%{$search}%");
                break;
            case 'nc':
                $query->where('NC', 'LIKE', "%{$search}%");
                break;
        }
    }
    
    // Ordenar por fecha de creación descendente
    $pacientes = $query->orderBy('created_at', 'desc')->paginate(10);
    $procedencias = Procedencia::all();
    $medicos = Medic::all();
    
    return view('pacientes.index', compact('pacientes', 'procedencias', 'medicos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $medicos = Medic::orderBy('nombre')->get();
        $procedencias = Procedencia::orderBy('Codigo')->get();
        
        return view('pacientes.formp', compact('medicos', 'procedencias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePacienteRequest $request)
    {
     \Log::info('Iniciando método store con datos válidos:', $request->all());
          
     try {
             \Log::info('Paciente creado exitosamente:', ['id' => $paciente->id]);
             $paciente = Paciente::create($request->all());
             return redirect()->route('pacientes.index')
                ->with('success', 'Paciente guardado correctamente!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al guardar: ' . $e->getMessage())
                ->withInput();
        }

            
    }

    /**
     * Display the specified resource.
     */
   public function show(Paciente $paciente)
    {
        
        $anexos = Anexo::all(); 
        $diagcitos = DiagCito::all(); 
        return view('pacientes.show', compact('paciente','anexos','diagcitos'));
    }

    public function showNB(Paciente $paciente)
    {
        
        $plantillas = Plantilla::all(); 
        return view('pacientes.show', compact('paciente','anexos','diagcitos'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Paciente $paciente)
    {
        $paciente = Paciente::with(['procedencia', 'medico'])->findOrFail($paciente->id);
        $procedencias = Procedencia::all();
        $medicos = Medic::all();
        return view('pacientes.edit', compact('paciente', 'procedencias', 'medicos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePacienteRequest $request, Paciente $paciente)
    {
         try {
            $paciente = Paciente::findOrFail($id);
            
            // Determinar el status según el botón presionado
            $status = $paciente->status; // Mantener el status actual por defecto
            
            if ($request->has('btnBiopsia')) {
                $status = 1; // Pendiente de biopsia
            } elseif ($request->has('btnCitologia')) {
                $status = 2; // Pendiente de citología
            }

            // Actualizar paciente
            $paciente->update([
                'CI' => $request->CI ?? $request->cedula,
                'Nombre' => $request->Nombre ?? $request->nombre,
                'Sexo' => $request->Sexo ?? $request->sexo,
                'Dirección' => $request->Dirección ?? $request->direccion,
                'Teléfono' => $request->Teléfono ?? $request->telefono,
                'Edad' => $request->Edad ?? $request->edad,
                'Procedencia' => $request->Procedencia ?? $request->procedencia,
                'Dr' => $request->Dr ?? $request->medicos,
                'mail' => $request->mail ?? $request->Mail,
                'NB' => $request->NB ?? $request->biopsia,
                'NC' => $request->NC ?? $request->citologia,
                'status' => $status
            ]);

            $mensaje = 'Paciente actualizado correctamente!';
            if ($status == 1) {
                $mensaje .= ' Pendiente de biopsia.';
            } elseif ($status == 2) {
                $mensaje .= ' Pendiente de citología.';
            }

            return redirect()->route('pacientes.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente eliminado exitosamente.');
    }
}
