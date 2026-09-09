<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CarteraController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ListaPrecioController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\VentaController;
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

    // Fase 6: Proveedores
    Route::resource('proveedores', ProveedorController::class)
        ->parameters(['proveedores' => 'proveedor'])
        ->except(['create', 'show', 'edit']);

    // Fase 6: Compras Comerciales
    Route::resource('compras', CompraController::class)
        ->parameters(['compras' => 'compra'])
        ->except(['edit', 'update', 'destroy']);
    Route::post('/compras/{compra}/anular', [CompraController::class, 'anular'])->name('compras.anular');

    // Fase 7: Clientes y Consumidor Final
    Route::resource('clientes', ClienteController::class);

    // Fase 8: Crédito y Cartera
    Route::prefix('cartera')->name('cartera.')->group(function () {
        Route::get('/', [CarteraController::class, 'index'])->name('index');
        Route::get('/nueva', [CarteraController::class, 'create'])->name('create');
        Route::post('/', [CarteraController::class, 'store'])->name('store');
        Route::get('/{cuenta}', [CarteraController::class, 'show'])->name('show');
        Route::post('/{cuenta}/abono', [CarteraController::class, 'storeAbono'])->name('abono.store');
        Route::get('/recibos/{pago}', [CarteraController::class, 'showRecibo'])->name('recibo');
        Route::post('/recibos/{pago}/anular', [CarteraController::class, 'anularAbono'])->name('recibo.anular');
        Route::get('/estado-cuenta/{cliente}', [CarteraController::class, 'estadoCuenta'])->name('estado-cuenta');
        Route::get('/estado-cuenta/{cliente}/imprimir', [CarteraController::class, 'printEstadoCuenta'])->name('estado-cuenta.print');
    });

    // Fase 9: Gestión de Cajas y Turnos
    Route::prefix('cajas')->name('cajas.')->group(function () {
        Route::get('/', [CajaController::class, 'index'])->name('index');
        Route::post('/', [CajaController::class, 'store'])->name('store');
        Route::get('/{caja}', [CajaController::class, 'show'])->name('show');
        Route::post('/{caja}/abrir', [CajaController::class, 'abrir'])->name('abrir');
        Route::post('/{caja}/movimiento', [CajaController::class, 'movimiento'])->name('movimiento');
        Route::get('/{caja}/cierre', [CajaController::class, 'cierre'])->name('cierre');
        Route::post('/{caja}/cierre', [CajaController::class, 'storeCierre'])->name('cierre.store');
        Route::get('/comprobantes/{sesion}', [CajaController::class, 'comprobante'])->name('comprobante');
    });

    // Fase 10: Motor de Ventas
    Route::prefix('ventas')->name('ventas.')->group(function () {
        Route::get('/', [VentaController::class, 'index'])->name('index');
        Route::post('/', [VentaController::class, 'store'])->name('store');
        Route::get('/{venta}', [VentaController::class, 'show'])->name('show');
        Route::post('/{venta}/anular', [VentaController::class, 'anular'])->name('anular');
        Route::get('/{venta}/ticket', [VentaController::class, 'ticket'])->name('ticket');
    });

    // Fase 11 & 12: Terminal Punto de Venta (POS) & Métodos de Pago
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/procesar', [PosController::class, 'procesar'])->name('procesar');
    });

    // Fase 13: Listas de Precios
    Route::prefix('listas-precios')->name('listas-precios.')->group(function () {
        Route::get('/', [ListaPrecioController::class, 'index'])->name('index');
        Route::get('/crear', [ListaPrecioController::class, 'create'])->name('create');
        Route::post('/', [ListaPrecioController::class, 'store'])->name('store');
        Route::get('/{listaPrecio}', [ListaPrecioController::class, 'show'])->name('show');
        Route::get('/{listaPrecio}/editar', [ListaPrecioController::class, 'edit'])->name('edit');
        Route::put('/{listaPrecio}', [ListaPrecioController::class, 'update'])->name('update');
        Route::delete('/{listaPrecio}', [ListaPrecioController::class, 'destroy'])->name('destroy');
        Route::post('/{listaPrecio}/precios', [ListaPrecioController::class, 'guardarPrecioProducto'])->name('precios.store');
        Route::delete('/{listaPrecio}/precios/{detalle}', [ListaPrecioController::class, 'eliminarPrecioProducto'])->name('precios.destroy');
    });
});



