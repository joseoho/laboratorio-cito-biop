<?php

use App\Http\Controllers\CaseController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('patients.index'));

// Registro público deshabilitado: en un sistema clínico los usuarios los crea un administrador.
Auth::routes(['register' => false]);

Route::get('/home', fn () => redirect()->route('patients.index'))
    ->middleware('auth')
    ->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Recepción: alta y corrección de pacientes y estudios (antes de las rutas con {patient})
    Route::middleware('role:admin,recepcion')->group(function () {
        Route::get('pacientes/crear', [PatientController::class, 'create'])->name('patients.create');
        Route::post('pacientes', [PatientController::class, 'store'])->name('patients.store');
        Route::get('pacientes/{patient}/editar', [PatientController::class, 'edit'])->whereNumber('patient')->name('patients.edit');
        Route::put('pacientes/{patient}', [PatientController::class, 'update'])->whereNumber('patient')->name('patients.update');

        Route::get('pacientes/{patient}/estudios/crear', [CaseController::class, 'create'])->whereNumber('patient')->name('cases.create');
        Route::post('pacientes/{patient}/estudios', [CaseController::class, 'store'])->whereNumber('patient')->name('cases.store');
    });

    // Lectura: cualquier usuario autenticado (incluye auditor)
    Route::get('pacientes', [PatientController::class, 'index'])->name('patients.index');
    Route::get('pacientes/{patient}', [PatientController::class, 'show'])->whereNumber('patient')->name('patients.show');

    Route::get('estudios', [CaseController::class, 'index'])->name('cases.index');
    Route::get('estudios/{case}', [CaseController::class, 'show'])->whereNumber('case')->name('cases.show');
    Route::get('estudios/{case}/imprimir', [CaseController::class, 'print'])->whereNumber('case')->name('cases.print');

    Route::get('reportes', [ReportController::class, 'index'])
        ->middleware('role:admin,patologo,auditor')
        ->name('reports.index');

    // Patólogo: escribe y corrige informes y datos del estudio
    Route::middleware('role:admin,patologo')->group(function () {
        Route::get('estudios/{case}/editar', [CaseController::class, 'edit'])->whereNumber('case')->name('cases.edit');
        Route::put('estudios/{case}', [CaseController::class, 'update'])->whereNumber('case')->name('cases.update');
        Route::get('estudios/{case}/datos', [CaseController::class, 'editHeader'])->whereNumber('case')->name('cases.header');
        Route::put('estudios/{case}/datos', [CaseController::class, 'updateHeader'])->whereNumber('case')->name('cases.header.update');
    });

    // Solo administración: reabrir un informe emitido
    Route::middleware('role:admin')->group(function () {
        Route::post('estudios/{case}/reabrir', [CaseController::class, 'reopen'])->whereNumber('case')->name('cases.reopen');
    });
});
