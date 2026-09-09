<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductoController;
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
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Gestión de Empresa
    Route::get('/empresa/perfil', [EmpresaController::class, 'perfil'])->name('empresa.perfil');
    Route::put('/empresa/perfil', [EmpresaController::class, 'updatePerfil'])->name('empresa.perfil.update');

    // Gestión de Sucursales
    Route::get('/sucursales', [SucursalController::class, 'index'])->name('sucursales.index');
    Route::post('/sucursales', [SucursalController::class, 'store'])->name('sucursales.store');
    Route::put('/sucursales/{sucursal}', [SucursalController::class, 'update'])->name('sucursales.update');
    Route::delete('/sucursales/{sucursal}', [SucursalController::class, 'destroy'])->name('sucursales.destroy');
    Route::post('/sucursales/seleccionar', [SucursalController::class, 'seleccionar'])->name('sucursales.seleccionar');

    // Fase 4: Catálogo de Productos y Clasificación
    Route::resource('productos', ProductoController::class);

    Route::prefix('catalogos')->name('catalogos.')->group(function () {
        Route::get('/', [CatalogoController::class, 'index'])->name('index');
        Route::post('/categorias', [CatalogoController::class, 'storeCategoria'])->name('categorias.store');
        Route::delete('/categorias/{categoria}', [CatalogoController::class, 'destroyCategoria'])->name('categorias.destroy');
        Route::post('/marcas', [CatalogoController::class, 'storeMarca'])->name('marcas.store');
        Route::delete('/marcas/{marca}', [CatalogoController::class, 'destroyMarca'])->name('marcas.destroy');
        Route::post('/unidades', [CatalogoController::class, 'storeUnidad'])->name('unidades.store');
        Route::delete('/unidades/{unidad}', [CatalogoController::class, 'destroyUnidad'])->name('unidades.destroy');
    });

    // Fase 5: Inventario y Kardex Inmutable
    Route::prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/', [InventarioController::class, 'index'])->name('index');
        Route::get('/kardex/{producto}', [InventarioController::class, 'kardex'])->name('kardex');
        Route::get('/ajuste', [InventarioController::class, 'createAjuste'])->name('ajuste.create');
        Route::post('/ajuste', [InventarioController::class, 'storeAjuste'])->name('ajuste.store');
        Route::get('/traslado', [InventarioController::class, 'createTraslado'])->name('traslado.create');
        Route::post('/traslado', [InventarioController::class, 'storeTraslado'])->name('traslado.store');
    });
});
