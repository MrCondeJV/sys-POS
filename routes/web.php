<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CarteraController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\DocumentoVentaController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FacturacionElectronicaController;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\ImpresionController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ListaPrecioController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProductoPresentacionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ResolucionFacturacionController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UserController;
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

    // Plataforma SaaS: Gestión de Empresas (Super Admin)
    Route::prefix('empresas')->name('empresas.')->group(function () {
        Route::get('/', [EmpresaController::class, 'index'])->name('index');
        Route::get('/crear', [EmpresaController::class, 'create'])->name('create');
        Route::post('/', [EmpresaController::class, 'store'])->name('store');
        Route::get('/{empresa}/editar', [EmpresaController::class, 'edit'])->name('edit');
        Route::put('/{empresa}', [EmpresaController::class, 'update'])->name('update');
        Route::post('/{empresa}/toggle-estado', [EmpresaController::class, 'toggleEstado'])->name('toggle-estado');
        Route::post('/seleccionar', [EmpresaController::class, 'seleccionar'])->name('seleccionar');
    });

    // Gestión de Empresa Activa
    Route::get('/empresa/perfil', [EmpresaController::class, 'perfil'])->name('empresa.perfil');
    Route::put('/empresa/perfil', [EmpresaController::class, 'updatePerfil'])->name('empresa.perfil.update');

    // Gestión de Usuarios y Colaboradores
    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/crear', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{usuario}/editar', [UserController::class, 'edit'])->name('edit');
        Route::put('/{usuario}', [UserController::class, 'update'])->name('update');
        Route::delete('/{usuario}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{usuario}/toggle-estado', [UserController::class, 'toggleEstado'])->name('toggle-estado');
    });

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

    // Fase 14: Devoluciones
    Route::prefix('devoluciones')->name('devoluciones.')->group(function () {
        Route::get('/', [DevolucionController::class, 'index'])->name('index');
        Route::get('/ventas/{venta}/crear', [DevolucionController::class, 'create'])->name('create');
        Route::post('/ventas/{venta}', [DevolucionController::class, 'store'])->name('store');
        Route::get('/{devolucion}', [DevolucionController::class, 'show'])->name('show');
        Route::get('/{devolucion}/comprobante', [DevolucionController::class, 'comprobante'])->name('comprobante');
    });

    // Fase 15: Reportes y Business Intelligence
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/ventas', [ReporteController::class, 'ventas'])->name('ventas');
        Route::get('/ventas/imprimir', [ReporteController::class, 'ventasImprimir'])->name('ventas.imprimir');
        Route::get('/compras', [ReporteController::class, 'compras'])->name('compras');
        Route::get('/compras/imprimir', [ReporteController::class, 'comprasImprimir'])->name('compras.imprimir');
        Route::get('/utilidad', [ReporteController::class, 'utilidad'])->name('utilidad');
        Route::get('/utilidad/imprimir', [ReporteController::class, 'utilidadImprimir'])->name('utilidad.imprimir');
        Route::get('/inventario', [ReporteController::class, 'inventario'])->name('inventario');
        Route::get('/inventario/imprimir', [ReporteController::class, 'inventarioImprimir'])->name('inventario.imprimir');
        Route::get('/cajas', [ReporteController::class, 'cajas'])->name('cajas');
        Route::get('/cajas/imprimir', [ReporteController::class, 'cajasImprimir'])->name('cajas.imprimir');
        Route::get('/cartera', [ReporteController::class, 'cartera'])->name('cartera');
        Route::get('/cartera/imprimir', [ReporteController::class, 'carteraImprimir'])->name('cartera.imprimir');
        Route::get('/metodos-pago', [ReporteController::class, 'metodosPago'])->name('metodos-pago');
        Route::get('/impuestos', [ReporteController::class, 'impuestos'])->name('impuestos');
    });

    // Fase 16: Auditoría y Trazabilidad
    Route::prefix('auditoria')->name('auditoria.')->group(function () {
        Route::get('/', [AuditoriaController::class, 'index'])->name('index');
        Route::get('/{auditoria}', [AuditoriaController::class, 'show'])->name('show');
    });

    // Fase 17: Impuestos Configurables
    Route::resource('impuestos', ImpuestoController::class);
    Route::post('/impuestos/{impuesto}/por-defecto', [ImpuestoController::class, 'hacerPorDefecto'])->name('impuestos.por-defecto');

    // Fase 18: Documentos Comerciales
    Route::resource('documentos', DocumentoVentaController::class)->only(['index', 'show']);
    Route::post('/documentos/{documento}/anular', [DocumentoVentaController::class, 'anular'])->name('documentos.anular');

    // Fase 19: Servicio Independiente de Impresión
    Route::prefix('imprimir')->name('imprimir.')->group(function () {
        Route::get('/documento/{documento}', [ImpresionController::class, 'imprimirDocumento'])->name('documento');
        Route::get('/venta/{venta}', [ImpresionController::class, 'imprimirVenta'])->name('venta');
        Route::get('/caja/{sesion}', [ImpresionController::class, 'imprimirCajaSesion'])->name('caja');
        Route::get('/abono/{abono}', [ImpresionController::class, 'imprimirAbono'])->name('abono');
    });

    // Fase 20: Notificaciones y Alertas del Sistema
    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        Route::get('/', [NotificacionController::class, 'index'])->name('index');
        Route::post('/{notificacion}/leida', [NotificacionController::class, 'marcarLeida'])->name('marcar-leida');
        Route::post('/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('marcar-todas');
        Route::get('/conteo', [NotificacionController::class, 'conteoNoLeidas'])->name('conteo');
    });

    // Fase 22: Facturación Electrónica DIAN
    Route::resource('resoluciones', ResolucionFacturacionController::class);
    Route::prefix('facturacion-electronica')->name('facturacion-electronica.')->group(function () {
        Route::get('/', [FacturacionElectronicaController::class, 'index'])->name('index');
        Route::get('/{documento}', [FacturacionElectronicaController::class, 'show'])->name('show');
        Route::post('/emitir/{documentoVenta}', [FacturacionElectronicaController::class, 'emitir'])->name('emitir');
        Route::post('/{documento}/nota-credito', [FacturacionElectronicaController::class, 'notaCredito'])->name('nota-credito');
        Route::get('/{documento}/descargar-xml', [FacturacionElectronicaController::class, 'descargarXml'])->name('descargar-xml');
    });

    // Fase 24: Ferreterías y Conversión de Unidades / Presentaciones
    Route::resource('productos.presentaciones', ProductoPresentacionController::class)->only(['index', 'store', 'destroy']);

    // FASE 25: Multisucursal y Traslados
    Route::get('reportes/multisucursal', [\App\Http\Controllers\ReporteMultisucursalController::class, 'index'])->name('reportes.multisucursal');
    Route::get('traslados', [\App\Http\Controllers\TrasladoSucursalController::class, 'index'])->name('traslados.index');
    Route::get('traslados/create', [\App\Http\Controllers\TrasladoSucursalController::class, 'create'])->name('traslados.create');
    Route::post('traslados', [\App\Http\Controllers\TrasladoSucursalController::class, 'store'])->name('traslados.store');
    Route::get('traslados/{traslado}', [\App\Http\Controllers\TrasladoSucursalController::class, 'show'])->name('traslados.show');
    Route::post('traslados/{traslado}/recibir', [\App\Http\Controllers\TrasladoSucursalController::class, 'recibir'])->name('traslados.recibir');
    Route::post('traslados/{traslado}/rechazar', [\App\Http\Controllers\TrasladoSucursalController::class, 'rechazar'])->name('traslados.rechazar');



    // FASE 27: PWA y Sincronización Offline POS
    Route::post('pos/sync-offline', [\App\Http\Controllers\PosOfflineSyncController::class, 'sync'])->name('pos.sync-offline');

});



