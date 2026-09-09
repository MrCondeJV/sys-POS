<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\SucursalController;
use Illuminate\Support\Facades\Route;

// Redirección de la raíz hacia el login o dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Rutas de Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Rutas Protegidas bajo Autenticación y Aislamiento Multiempresa
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Gestión de Empresa
    Route::get('/empresa/perfil', [EmpresaController::class, 'perfil'])->name('empresa.perfil');
    Route::put('/empresa/perfil', [EmpresaController::class, 'updatePerfil'])->name('empresa.perfil.update');

    // Gestión de Sucursales
    Route::get('/sucursales', [SucursalController::class, 'index'])->name('sucursales.index');
    Route::post('/sucursales', [SucursalController::class, 'store'])->name('sucursales.store');
    Route::put('/sucursales/{sucursal}', [SucursalController::class, 'update'])->name('sucursales.update');
    Route::delete('/sucursales/{sucursal}', [SucursalController::class, 'destroy'])->name('sucursales.destroy');
    Route::post('/sucursales/seleccionar', [SucursalController::class, 'seleccionar'])->name('sucursales.seleccionar');
});
