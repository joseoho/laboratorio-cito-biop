<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PlantillaNBController;
use App\Http\Controllers\BiopsiaController;
use App\Http\Controllers\CitologiaController;
use App\Http\Controllers\MedicController;
use App\Http\Controllers\PatologoController;
use App\Http\Controllers\ProcedenciaController;
use App\Http\Controllers\AnexoController;
use App\Http\Controllers\DiagCitoController;

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Auth::routes();


Route::middleware(['auth', 'verified'])->group(function () {
Route::resource('pacientes', PacienteController::class);
Route::resource('medicos', MedicController::class);

Route::resource('patologos', PatologoController::class);  
Route::resource('procedencias', ProcedenciaController::class);
// Route::get('procedencias/get/{id}', 'ProcedenciaController@getProcedencia')->name('procedencias.get');
Route::resource('anexos', AnexoController::class);
Route::resource('plantillas', PlantillaNBController::class);
Route::resource('diagcito', DiagCitoController::class);
Route::resource('biopsias', BiopsiaController::class);
Route::resource('citologias', CitologiaController::class);
Route::get('citologias/{id}/imprimir', [CitologiaController::class, 'imprimir'])->name('citologias.imprimir');
Route::get('citologias/{id}/vista-previa', [CitologiaController::class, 'vistaPrevia'])->name('citologias.vista-previa');
// Route::resource('citologias', CitologiaController::class)->except(['create']);
// Ruta personalizada para crear citología con datos de paciente
// Route::get('citologias/crear/{paciente}', [CitologiaController::class, 'crear'])->name('citologias.crear');
// Route::post('/buscar/cedula', [PacienteController::class, 'buscarPorCedula'])->name('buscar.cedula');
// Route::post('/buscar/nb', [PacienteController::class, 'buscarPorNB'])->name('buscar.nb');
// Route::post('/buscar/nc', [PacienteController::class, 'buscarPorNC'])->name('buscar.nc');
// Rutas para formularios específicos
// Route::get('/biopsia/{numero}', [BiopsiaController::class, 'show'])->name('biopsia.form');
// Route::get('/citologia/{numero}', [CitologiaController::class, 'show'])->name('citologia.form');

});