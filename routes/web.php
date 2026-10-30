<?php

use App\Http\Controllers\CaseController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('patients.index'));

// Registro público deshabilitado: en un sistema clínico los usuarios los crea un administrador.
Auth::routes(['register' => false]);

Route::get('/home', fn () => redirect()->route('patients.index'))
    ->middleware('auth')
    ->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('pacientes', [PatientController::class, 'index'])->name('patients.index');
    Route::get('pacientes/crear', [PatientController::class, 'create'])->name('patients.create');
    Route::post('pacientes', [PatientController::class, 'store'])->name('patients.store');
    Route::get('pacientes/{patient}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('pacientes/{patient}/editar', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('pacientes/{patient}', [PatientController::class, 'update'])->name('patients.update');

    // Estudios
    Route::get('pacientes/{patient}/estudios/crear', [CaseController::class, 'create'])->name('cases.create');
    Route::post('pacientes/{patient}/estudios', [CaseController::class, 'store'])->name('cases.store');

    Route::get('estudios', [CaseController::class, 'index'])->name('cases.index');
    Route::get('estudios/{case}', [CaseController::class, 'show'])->name('cases.show');
    Route::get('estudios/{case}/editar', [CaseController::class, 'edit'])->name('cases.edit');
    Route::put('estudios/{case}', [CaseController::class, 'update'])->name('cases.update');
    Route::get('estudios/{case}/datos', [CaseController::class, 'editHeader'])->name('cases.header');
    Route::put('estudios/{case}/datos', [CaseController::class, 'updateHeader'])->name('cases.header.update');
    Route::get('estudios/{case}/imprimir', [CaseController::class, 'print'])->name('cases.print');
});
