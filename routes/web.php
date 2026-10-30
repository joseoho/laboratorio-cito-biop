<?php

use App\Http\Controllers\BackupController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\QuarantineController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('patients.index'));

// Registro público deshabilitado: en un sistema clínico los usuarios los crea un administrador.
Auth::routes(['register' => false]);

Route::get('/home', fn () => redirect()->route('patients.index'))
    ->middleware('auth')
    ->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Operación diaria: alta, corrección e informes (antes de las rutas con {patient})
    Route::middleware('role:admin,operador')->group(function () {
        Route::get('pacientes/crear', [PatientController::class, 'create'])->name('patients.create');
        Route::post('pacientes', [PatientController::class, 'store'])->name('patients.store');
        Route::get('pacientes/{patient}/editar', [PatientController::class, 'edit'])->whereNumber('patient')->name('patients.edit');
        Route::put('pacientes/{patient}', [PatientController::class, 'update'])->whereNumber('patient')->name('patients.update');

        Route::get('pacientes/{patient}/estudios/crear', [CaseController::class, 'create'])->whereNumber('patient')->name('cases.create');
        Route::post('pacientes/{patient}/estudios', [CaseController::class, 'store'])->whereNumber('patient')->name('cases.store');

        Route::get('estudios/{case}/editar', [CaseController::class, 'edit'])->whereNumber('case')->name('cases.edit');
        Route::put('estudios/{case}', [CaseController::class, 'update'])->whereNumber('case')->name('cases.update');
        Route::get('estudios/{case}/datos', [CaseController::class, 'editHeader'])->whereNumber('case')->name('cases.header');
        Route::put('estudios/{case}/datos', [CaseController::class, 'updateHeader'])->whereNumber('case')->name('cases.header.update');
    });

    // Lectura
    Route::get('pacientes', [PatientController::class, 'index'])->name('patients.index');
    Route::get('pacientes/{patient}', [PatientController::class, 'show'])->whereNumber('patient')->name('patients.show');

    Route::get('estudios', [CaseController::class, 'index'])->name('cases.index');
    Route::get('estudios/{case}', [CaseController::class, 'show'])->whereNumber('case')->name('cases.show');
    Route::get('estudios/{case}/imprimir', [CaseController::class, 'print'])->whereNumber('case')->name('cases.print');
    Route::get('estudios/{case}/pdf', [CaseController::class, 'downloadPdf'])->whereNumber('case')->name('cases.pdf');

    Route::get('reportes', [ReportController::class, 'index'])
        ->middleware('role:admin,operador')
        ->name('reports.index');

    // Solo administración: reabrir emitidos, cuarentena y respaldos
    Route::middleware('role:admin')->group(function () {
        Route::post('estudios/{case}/reabrir', [CaseController::class, 'reopen'])->whereNumber('case')->name('cases.reopen');

        Route::get('cuarentena', [QuarantineController::class, 'index'])->name('quarantine.index');
        Route::post('cuarentena/{quarantine}/resolver', [QuarantineController::class, 'resolve'])
            ->whereNumber('quarantine')
            ->name('quarantine.resolve');

        Route::get('respaldos', [BackupController::class, 'index'])->name('backups.index');
        Route::post('respaldos', [BackupController::class, 'store'])->name('backups.store');
        Route::get('respaldos/{file}', [BackupController::class, 'download'])->name('backups.download');

        Route::get('usuarios', [UserController::class, 'index'])->name('users.index');
        Route::get('usuarios/crear', [UserController::class, 'create'])->name('users.create');
        Route::post('usuarios', [UserController::class, 'store'])->name('users.store');
        Route::get('usuarios/{user}/editar', [UserController::class, 'edit'])->whereNumber('user')->name('users.edit');
        Route::put('usuarios/{user}', [UserController::class, 'update'])->whereNumber('user')->name('users.update');
        Route::delete('usuarios/{user}', [UserController::class, 'destroy'])->whereNumber('user')->name('users.destroy');
    });
});
