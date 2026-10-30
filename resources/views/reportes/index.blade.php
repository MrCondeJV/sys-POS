@extends('layouts.app')

@section('title', 'Centro de Reportes')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <x-page-header
        title="Centro de Reportes y Analítica"
        subtitle="Consulte métricas financieras, inventario, ventas, cartera y balance fiscal en tiempo real.">
        <x-slot:actions>
            <a href="{{ route('dashboard') }}" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al Dashboard
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Categorías de Reportes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Ventas -->
        <a href="{{ route('reportes.ventas') }}" class="card p-5 hover:border-brand-400 hover:shadow-md transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="p-2.5 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-brand-50 group-hover:text-brand-700 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </span>
                    <span class="kpi-label">Comercial</span>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 transition">Ventas Detalladas</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Listado detallado de transacciones, filtrado por fechas, clientes, sucursales y cajeros con cálculo de ticket promedio.</p>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-semibold text-brand-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 2. Utilidad y Rentabilidad -->
        <a href="{{ route('reportes.utilidad') }}" class="card p-5 hover:border-brand-400 hover:shadow-md transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="p-2.5 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-brand-50 group-hover:text-brand-700 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </span>
                    <span class="kpi-label">Finanzas</span>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 transition">Utilidad y Rentabilidad</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Margen bruto y porcentaje de ganancia real calculando costo unitario histórico vs precio de venta por artículo.</p>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-semibold text-brand-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 3. Inventario y Valoración -->
        <a href="{{ route('reportes.inventario') }}" class="card p-5 hover:border-brand-400 hover:shadow-md transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="p-2.5 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-brand-50 group-hover:text-brand-700 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </span>
                    <span class="kpi-label">Bodega</span>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 transition">Valoración de Stock</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Costo total valorizado de mercancía, valor comercial proyectado y alertas de niveles mínimos de inventario.</p>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-semibold text-brand-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 4. Compras -->
        <a href="{{ route('reportes.compras') }}" class="card p-5 hover:border-brand-400 hover:shadow-md transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="p-2.5 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-brand-50 group-hover:text-brand-700 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <span class="kpi-label">Abastecimiento</span>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 transition">Compras y Proveedores</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Registro consolidado de compras a proveedores, costos de adquisición e impuestos aplicados en facturas recibidas.</p>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-semibold text-brand-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 5. Cajas y Turnos -->
        <a href="{{ route('reportes.cajas') }}" class="card p-5 hover:border-brand-400 hover:shadow-md transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="p-2.5 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-brand-50 group-hover:text-brand-700 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                    <span class="kpi-label">Cajas</span>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 transition">Cajas y Arqueos</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Auditoría de aperturas y cierres de turno, diferencias de arqueo (faltantes y sobrantes) y control de efectivo.</p>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-semibold text-brand-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 6. Cartera y Envejecimiento -->
        <a href="{{ route('reportes.cartera') }}" class="card p-5 hover:border-brand-400 hover:shadow-md transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="p-2.5 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-brand-50 group-hover:text-brand-700 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="kpi-label">Créditos</span>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 transition">Cartera por Cobrar</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Ventas fiadas por cobrar clasificadas por antigüedad desde la venta: hasta 30, 31-60, 61-90 y más de 90 días.</p>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-semibold text-brand-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 7. Métodos de Pago -->
        <a href="{{ route('reportes.metodos-pago') }}" class="card p-5 hover:border-brand-400 hover:shadow-md transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="p-2.5 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-brand-50 group-hover:text-brand-700 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </span>
                    <span class="kpi-label">Recaudos</span>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 transition">Métodos de Pago</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Desglose porcentual y montos totales capturados vía Efectivo, Tarjeta, Transferencia y Mixto en un rango de tiempo.</p>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-semibold text-brand-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- 8. Impuestos e IVA -->
        <a href="{{ route('reportes.impuestos') }}" class="card p-5 hover:border-brand-400 hover:shadow-md transition group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="p-2.5 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-brand-50 group-hover:text-brand-700 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                        </svg>
                    </span>
                    <span class="kpi-label">Fiscal</span>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 group-hover:text-brand-700 transition">Impuestos y Balance Fiscal</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Base gravable consolidada, IVA generado en ventas e IVA descontable en compras para liquidación tributaria.</p>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-semibold text-brand-600">
                <span>Generar Reporte</span>
                <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </a>
    </div>
</div>
@endsection
