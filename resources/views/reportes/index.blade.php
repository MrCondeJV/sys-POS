@extends('layouts.app')

@section('title', 'Centro de Reportes')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-2.5 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
                Centro de Reportes y Analítica
            </h1>
            <p class="text-sm text-slate-500 mt-1">Consulte métricas financieras, inventario, ventas, cartera y balance fiscal en tiempo real.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition shadow-sm">
                &larr; Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Categorías de Reportes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- 1. Ventas -->
        <a href="{{ route('reportes.ventas') }}" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-lg transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="p-3 rounded-2xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase">Comercial</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition">Ventas Detalladas</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">Listado detallado de transacciones, filtrado por fechas, clientes, sucursales y cajeros con cálculo de ticket promedio.</p>
            </div>
            <div class="mt-6 flex items-center justify-between text-xs font-bold text-indigo-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- 2. Utilidad y Rentabilidad -->
        <a href="{{ route('reportes.utilidad') }}" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-lg transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="p-3 rounded-2xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase">Finanzas</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition">Utilidad y Rentabilidad</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">Margen bruto y porcentaje de ganancia real calculando costo unitario histórico vs precio de venta por artículo.</p>
            </div>
            <div class="mt-6 flex items-center justify-between text-xs font-bold text-indigo-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- 3. Inventario y Valoración -->
        <a href="{{ route('reportes.inventario') }}" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-lg transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="p-3 rounded-2xl bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase">Bodega</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition">Valoración de Stock</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">Costo total valorizado de mercancía, valor comercial proyectado y alertas de niveles mínimos de inventario.</p>
            </div>
            <div class="mt-6 flex items-center justify-between text-xs font-bold text-indigo-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- 4. Compras -->
        <a href="{{ route('reportes.compras') }}" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-lg transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="p-3 rounded-2xl bg-cyan-50 text-cyan-600 group-hover:bg-cyan-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase">Abastecimiento</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition">Compras y Proveedores</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">Registro consolidado de compras a proveedores, costos de adquisición e impuestos aplicados en facturas recibidas.</p>
            </div>
            <div class="mt-6 flex items-center justify-between text-xs font-bold text-indigo-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- 5. Cajas y Turnos -->
        <a href="{{ route('reportes.cajas') }}" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-lg transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="p-3 rounded-2xl bg-violet-50 text-violet-600 group-hover:bg-violet-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase">Cajas</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition">Cajas y Arqueos</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">Auditoría de aperturas y cierres de turno, diferencias de arqueo (faltantes y sobrantes) y control de efectivo.</p>
            </div>
            <div class="mt-6 flex items-center justify-between text-xs font-bold text-indigo-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- 6. Cartera y Envejecimiento -->
        <a href="{{ route('reportes.cartera') }}" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-lg transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="p-3 rounded-2xl bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase">Créditos</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition">Cartera y Mora</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">Cuentas por cobrar clasificadas por antigüedad de vencimiento: Corriente, 1-30, 31-60 y más de 60 días de mora.</p>
            </div>
            <div class="mt-6 flex items-center justify-between text-xs font-bold text-indigo-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- 7. Métodos de Pago -->
        <a href="{{ route('reportes.metodos-pago') }}" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-lg transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="p-3 rounded-2xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase">Recaudos</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition">Métodos de Pago</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">Desglose porcentual y montos totales capturados vía Efectivo, Tarjeta, Transferencia y Mixto en un rango de tiempo.</p>
            </div>
            <div class="mt-6 flex items-center justify-between text-xs font-bold text-indigo-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- 8. Impuestos e IVA -->
        <a href="{{ route('reportes.impuestos') }}" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-lg transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="p-3 rounded-2xl bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                        </svg>
                    </span>
                    <span class="text-xs font-bold text-slate-400 uppercase">Fiscal</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition">Impuestos y Balance Fiscal</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">Base gravable consolidada, IVA generado en ventas e IVA descontable en compras para liquidación tributaria.</p>
            </div>
            <div class="mt-6 flex items-center justify-between text-xs font-bold text-indigo-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>
    </div>
</div>
@endsection
