@extends('layouts.app')

@section('title', 'Design System Preview — Banca Corporativa Moderna')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto pb-16">
    {{-- Page Header Component --}}
    <x-page-header
        title="Sistema de Diseño — Banca Corporativa Moderna"
        subtitle="Tokens visuales y componentes base inspirados en Stripe, Linear y Mercury."
    >
        <x-slot:breadcrumb>
            <span>Configuración</span>
            <span class="text-slate-300">/</span>
            <span class="text-slate-700 font-medium">Design System</span>
        </x-slot:breadcrumb>

        <x-slot:actions>
            <x-button variant="outline" size="sm" icon="<svg class='w-3.5 h-3.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'/></svg>">
                Exportar Tokens
            </x-button>
            <x-button variant="primary" size="sm" icon="<svg class='w-3.5 h-3.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4'/></svg>">
                Nueva Operación
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- KPI Cards Section --}}
    <div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-3">
            Tarjetas de Métricas (KPI Cards)
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-kpi-card
                label="Ventas de Hoy"
                value="$1,425,800"
                context="32 transacciones"
                trend="up"
                trendValue="+14.2%"
            />

            <x-kpi-card
                label="Cartera por Cobrar"
                value="$4,280,000"
                context="12 clientes pendientes"
                trend="down"
                trendValue="-3.1%"
                alert="warning"
            />

            <x-kpi-card
                label="Stock Crítico"
                value="8 ítems"
                context="Requieren reposición inmediata"
                alert="danger"
            />

            <x-kpi-card
                label="Caja Principal"
                value="$350,000"
                context="Turno N° 104 en curso"
                trend="neutral"
                trendValue="0.0%"
            />
        </div>
    </div>

    {{-- Badges & Status Badges Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Badges Semánticos Base (&lt;x-badge&gt;)">
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-medium text-slate-500 mb-2">Variantes de Color (dot=true):</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge variant="success" dot>Completado</x-badge>
                        <x-badge variant="warning" dot>Pendiente</x-badge>
                        <x-badge variant="danger" dot>Anulado</x-badge>
                        <x-badge variant="info" dot>Procesando</x-badge>
                        <x-badge variant="neutral" dot>Borrador</x-badge>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-medium text-slate-500 mb-2">Tamaños (sm vs md):</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge variant="success" size="sm">Small (18px)</x-badge>
                        <x-badge variant="success" size="md">Medium (22px)</x-badge>
                        <x-badge variant="neutral" size="sm">v1.2.0</x-badge>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-medium text-slate-500 mb-2">Atajos de Teclado (<kbd class="kbd">Ctrl</kbd> + <kbd class="kbd">K</kbd>):</p>
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <span>Buscar producto:</span>
                        <kbd class="kbd">F2</kbd>
                        <span>Cobrar venta:</span>
                        <kbd class="kbd">F9</kbd>
                        <span>Cancelar:</span>
                        <kbd class="kbd">Esc</kbd>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card title="Mapeador de Enums (&lt;x-status-badge&gt;)">
            <div class="space-y-3">
                <p class="text-xs text-slate-500">Mapeo automático de colores y etiquetas para modelos de dominio:</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="flex items-center justify-between p-2 rounded bg-slate-50 border border-slate-100">
                        <span class="text-slate-600 font-mono text-[11px]">EstadoVenta:</span>
                        <x-status-badge status="COMPLETADA" />
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-slate-50 border border-slate-100">
                        <span class="text-slate-600 font-mono text-[11px]">EstadoVenta:</span>
                        <x-status-badge status="ANULADA" />
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-slate-50 border border-slate-100">
                        <span class="text-slate-600 font-mono text-[11px]">EstadoCaja:</span>
                        <x-status-badge status="ABIERTA" />
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-slate-50 border border-slate-100">
                        <span class="text-slate-600 font-mono text-[11px]">EstadoCaja:</span>
                        <x-status-badge status="CERRADA" />
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-slate-50 border border-slate-100">
                        <span class="text-slate-600 font-mono text-[11px]">CuentaCobrar:</span>
                        <x-status-badge status="PARCIAL" />
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-slate-50 border border-slate-100">
                        <span class="text-slate-600 font-mono text-[11px]">CuentaCobrar:</span>
                        <x-status-badge status="PAGADA" />
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-slate-50 border border-slate-100">
                        <span class="text-slate-600 font-mono text-[11px]">EstadoGeneral:</span>
                        <x-status-badge status="ACTIVO" />
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-slate-50 border border-slate-100">
                        <span class="text-slate-600 font-mono text-[11px]">Suscripcion:</span>
                        <x-status-badge status="PRUEBA" />
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Buttons & Icon Buttons Section --}}
    <x-card title="Botones Institucionales (&lt;x-button&gt; &amp; &lt;x-icon-button&gt;)">
        <div class="space-y-5">
            <div>
                <p class="text-xs font-medium text-slate-500 mb-2.5">Variantes Principales:</p>
                <div class="flex flex-wrap items-center gap-3">
                    <x-button variant="primary">Primary Button</x-button>
                    <x-button variant="secondary">Secondary Button</x-button>
                    <x-button variant="outline">Outline Button</x-button>
                    <x-button variant="danger">Danger Button</x-button>
                    <x-button variant="ghost">Ghost Button</x-button>
                </div>
            </div>

            <div>
                <p class="text-xs font-medium text-slate-500 mb-2.5">Tamaños (sm 32px, md 36px, lg 40px) y Estados:</p>
                <div class="flex flex-wrap items-center gap-3">
                    <x-button variant="primary" size="sm">Small (32px)</x-button>
                    <x-button variant="primary" size="md">Medium (36px)</x-button>
                    <x-button variant="primary" size="lg">Large (40px)</x-button>
                    <x-button variant="primary" disabled>Deshabilitado</x-button>
                    <x-button variant="primary" loading>Guardando...</x-button>
                </div>
            </div>

            <div>
                <p class="text-xs font-medium text-slate-500 mb-2.5">Botones Cuadrados de Acción (&lt;x-icon-button&gt;):</p>
                <div class="flex items-center gap-2">
                    <x-icon-button variant="ghost" title="Ver detalle">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </x-icon-button>

                    <x-icon-button variant="secondary" title="Editar registro">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </x-icon-button>

                    <x-icon-button variant="outline" title="Descargar PDF">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </x-icon-button>

                    <x-icon-button variant="danger" title="Eliminar registro">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </x-icon-button>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Form Fields & Modals Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Campos de Formulario (&lt;x-field&gt;)">
            <div class="space-y-4">
                <x-field label="Razón Social / Nombre" name="nombre" required hint="Tal como aparece en el documento fiscal.">
                    <input type="text" id="nombre" name="nombre" value="Ferretería El Progreso S.A.S." class="input" />
                </x-field>

                <x-field label="Correo Electrónico de Facturación" name="email" required error="Este correo ya se encuentra registrado en el sistema.">
                    <input type="email" id="email" name="email" value="contacto@empresa.com" class="input border-danger" />
                </x-field>
            </div>
        </x-card>

        <x-card title="Modales de Confirmación y Shimmer">
            <div class="space-y-5">
                <div>
                    <p class="text-xs font-medium text-slate-500 mb-2">Modal Accesible con Alpine.js:</p>
                    <x-modal-confirm
                        name="eliminar-ejemplo"
                        title="¿Anular esta factura electrónica?"
                        message="Esta acción emitirá una Nota de Crédito ante la DIAN y revertirá los saldos de inventario asociados. No se puede deshacer."
                        confirmText="Sí, anular factura"
                        variant="danger"
                    >
                        <x-slot:trigger>
                            <x-button variant="danger" size="sm">
                                Abrir Modal Confirmación
                            </x-button>
                        </x-slot:trigger>
                    </x-modal-confirm>
                </div>

                <div>
                    <p class="text-xs font-medium text-slate-500 mb-2">Efecto Shimmer / Carga (.skeleton):</p>
                    <div class="space-y-2">
                        <div class="skeleton h-4 w-3/4"></div>
                        <div class="skeleton h-4 w-1/2"></div>
                        <div class="skeleton h-8 w-full"></div>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Banking Table Component --}}
    <div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-3">
            Tabla de Datos Institucional (&lt;x-table&gt;)
        </h2>

        <x-table>
            <x-slot:header>
                <tr>
                    <th>Comprobante</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th class="text-right">Monto Total</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </x-slot:header>

            <tr>
                <td class="font-mono font-medium text-slate-900">FAC-2026-0042</td>
                <td>
                    <div class="font-medium text-slate-900">Inversiones Andinas S.A.</div>
                    <div class="text-xs text-slate-400">NIT: 900.123.456-7</div>
                </td>
                <td class="text-slate-500">19 Oct, 14:32</td>
                <td class="text-right font-semibold text-slate-900 tabular-nums">$1,250,000</td>
                <td><x-status-badge status="COMPLETADA" /></td>
                <td class="text-right">
                    <x-icon-button variant="ghost" title="Ver detalle">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-icon-button>
                </td>
            </tr>

            <tr>
                <td class="font-mono font-medium text-slate-900">FAC-2026-0041</td>
                <td>
                    <div class="font-medium text-slate-900">Distribuidora Central</div>
                    <div class="text-xs text-slate-400">NIT: 800.987.654-1</div>
                </td>
                <td class="text-slate-500">19 Oct, 11:15</td>
                <td class="text-right font-semibold text-slate-900 tabular-nums">$480,000</td>
                <td><x-status-badge status="PARCIAL" /></td>
                <td class="text-right">
                    <x-icon-button variant="ghost" title="Ver detalle">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-icon-button>
                </td>
            </tr>

            <tr>
                <td class="font-mono font-medium text-slate-900">FAC-2026-0040</td>
                <td>
                    <div class="font-medium text-slate-900">Consumidor Final</div>
                    <div class="text-xs text-slate-400">CC: 222222222222</div>
                </td>
                <td class="text-slate-500">19 Oct, 09:40</td>
                <td class="text-right font-semibold text-slate-900 tabular-nums">$65,000</td>
                <td><x-status-badge status="ANULADA" /></td>
                <td class="text-right">
                    <x-icon-button variant="ghost" title="Ver detalle">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-icon-button>
                </td>
            </tr>

            <x-slot:footer>
                <div class="text-xs text-slate-500">
                    Mostrando 3 de 142 registros
                </div>
                <div class="flex items-center gap-1.5">
                    <x-button variant="secondary" size="sm" disabled>Anterior</x-button>
                    <x-button variant="secondary" size="sm">Siguiente</x-button>
                </div>
            </x-slot:footer>
        </x-table>
    </div>

    {{-- Empty State Component --}}
    <div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-3">
            Estado Vacío (&lt;x-empty-state&gt;)
        </h2>
        <x-empty-state
            title="No hay órdenes de compra pendientes"
            description="Todos los pedidos a proveedores han sido recibidos o procesados satisfactoriamente."
        >
            <x-button variant="primary" size="sm" icon="<svg class='w-3.5 h-3.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4'/></svg>">
                Generar Orden de Compra
            </x-button>
        </x-empty-state>
    </div>

    {{-- Toast Component Preview --}}
    <x-toast />
</div>
@endsection
