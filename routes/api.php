<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CajaController;
use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\CompraController;
use App\Http\Controllers\Api\V1\EmpresaController;
use App\Http\Controllers\Api\V1\InventarioController;
use App\Http\Controllers\Api\V1\ProductoController;
use App\Http\Controllers\Api\V1\ProveedorController;
use App\Http\Controllers\Api\V1\ReporteController;
use App\Http\Controllers\Api\V1\SucursalController;
use App\Http\Controllers\Api\V1\VentaController;
use App\Http\Middleware\InitializeApiCompanyContext;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistema POS Comercial (/api/v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // 1. Auth pública
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.v1.auth.login');

    // 2. Endpoints protegidos con Sanctum y contexto multi-tenant
    Route::middleware(['auth:sanctum', InitializeApiCompanyContext::class])->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');

        // Empresa
        Route::get('/empresa', [EmpresaController::class, 'show'])->name('api.v1.empresa.show');

        // Sucursales
        Route::get('/sucursales', [SucursalController::class, 'index'])->name('api.v1.sucursales.index');
        Route::get('/sucursales/{sucursal}', [SucursalController::class, 'show'])->name('api.v1.sucursales.show');

        // Productos
        Route::get('/productos', [ProductoController::class, 'index'])->name('api.v1.productos.index');
        Route::get('/productos/{producto}', [ProductoController::class, 'show'])->name('api.v1.productos.show');

        // Inventario & Kardex
        Route::get('/inventario', [InventarioController::class, 'index'])->name('api.v1.inventario.index');
        Route::get('/inventario/kardex', [InventarioController::class, 'kardex'])->name('api.v1.inventario.kardex');

        // Clientes
        Route::get('/clientes', [ClienteController::class, 'index'])->name('api.v1.clientes.index');
        Route::post('/clientes', [ClienteController::class, 'store'])->name('api.v1.clientes.store');
        Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('api.v1.clientes.show');

        // Proveedores
        Route::get('/proveedores', [ProveedorController::class, 'index'])->name('api.v1.proveedores.index');
        Route::get('/proveedores/{proveedor}', [ProveedorController::class, 'show'])->name('api.v1.proveedores.show');

        // Ventas / POS
        Route::get('/ventas', [VentaController::class, 'index'])->name('api.v1.ventas.index');
        Route::post('/ventas', [VentaController::class, 'store'])->name('api.v1.ventas.store');
        Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('api.v1.ventas.show');

        // Compras
        Route::get('/compras', [CompraController::class, 'index'])->name('api.v1.compras.index');
        Route::get('/compras/{compra}', [CompraController::class, 'show'])->name('api.v1.compras.show');

        // Caja
        Route::get('/caja/estado', [CajaController::class, 'estado'])->name('api.v1.caja.estado');
        Route::post('/caja/abrir', [CajaController::class, 'abrir'])->name('api.v1.caja.abrir');
        Route::post('/caja/cerrar', [CajaController::class, 'cerrar'])->name('api.v1.caja.cerrar');
        Route::get('/caja/{sesion}/movimientos', [CajaController::class, 'movimientos'])->name('api.v1.caja.movimientos');

        // Reportes
        Route::get('/reportes/resumen', [ReporteController::class, 'resumen'])->name('api.v1.reportes.resumen');
    });
});
