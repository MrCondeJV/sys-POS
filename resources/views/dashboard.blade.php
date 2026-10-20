@extends('layouts.app')

@section('title', 'Dashboard Operativo')

@section('content')
@php
    $hora = (int) now()->format('H');
    if ($hora < 12) {
        $saludo = 'Buenos días';
    } elseif ($hora < 19) {
        $saludo = 'Buenas tardes';
    } else {
        $saludo = 'Buenas noches';
    }

    $fechaEspanol = ucfirst(\Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY'));
    $empresaActualDash = \App\Support\Tenancy\CompanyContext::getCompany() ?? auth()->user()->empresa;
    $empresaNombre = $empresaActualDash?->nombre_comercial ?? 'POS Comercial';
    $simboloMoneda = $empresaActualDash?->simbolo_moneda ?? '$';
    $sedeNombre = \App\Support\Tenancy\BranchContext::getBranch()?->nombre ?? 'Sede Principal';

    $ventas7Dias = $ventasUltimos7Dias ?? [];
    $totalSemana = array_sum(array_column($ventas7Dias, 'total'));
    $maxVentaDia = !empty($ventas7Dias) ? (max(array_column($ventas7Dias, 'total')) ?: 1) : 1;

    $sesionCajaActiva = null;
    try {
        $sesionCajaActiva = \App\Models\SesionCaja::with(['caja', 'cajero'])
            ->where('estado', \App\Enums\EstadoSesionCaja::ABIERTA->value)
            ->latest('fecha_apertura')
            ->first();
    } catch (\Throwable $e) {
        $sesionCajaActiva = null;
    }

    $prodCriticos = $productosCriticos ?? collect();
    $ultimosProd = $ultimosProductos ?? collect();
    $articulosAMostrar = $prodCriticos->isNotEmpty() ? $prodCriticos : $ultimosProd;
    $maxStockRef = max(1, (float) ($articulosAMostrar->max('stock') ?: 10));
    $listaUltimasVentas = $ultimasVentas ?? collect();
@endphp

<div class="w-full max-w-[1360px] mx-auto space-y-6">
    <!-- Encabezado de la Página -->
    <x-page-header
        :title="$saludo . ', ' . auth()->user()->name"
        :subtitle="'Resumen operativo de ' . $empresaNombre . ' · ' . $sedeNombre . ' — ' . $fechaEspanol"
    >
        <x-slot:actions>
            @can('productos.crear')
                <x-button variant="secondary" size="md" href="{{ route('productos.create') }}"
                    icon="<svg class='w-4 h-4 text-text-secondary' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='1.75'><path stroke-linecap='round' stroke-linejoin='round' d='M12 4v16m8-8H4' /></svg>">
                    Nuevo producto
                </x-button>
            @elsecan('productos.ver')
                <x-button variant="secondary" size="md" href="{{ route('productos.index') }}"
                    icon="<svg class='w-4 h-4 text-text-secondary' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='1.75'><path stroke-linecap='round' stroke-linejoin='round' d='M4 6h16M4 10h16M4 14h16M4 18h16' /></svg>">
                    Ver catálogo
                </x-button>
            @endcan

            @can('ventas.crear')
                <x-button variant="primary" size="md" href="{{ route('pos.index') }}"
                    icon="<svg class='w-4 h-4 text-white' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='1.75'><path stroke-linecap='round' stroke-linejoin='round' d='M12 4v16m8-8H4' /></svg>">
                    Nueva venta
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <!-- Fila 1: 4 KPIs Principales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Ventas de Hoy"
            :value="$simboloMoneda . number_format($ventasHoy ?? 0, 2)"
            :context="($cantidadVentasHoy ?? 0) > 0 ? (($cantidadVentasHoy ?? 0) . ' transacciones hoy') : 'Aún sin transacciones hoy'"
            icon="cash"
            :href="route('reportes.ventas')"
        />

        <x-kpi-card
            label="Mes Actual"
            :value="$simboloMoneda . number_format($ventasMes ?? 0, 2)"
            :context="($cantidadVentasMes ?? 0) . ' ventas · Prom. ' . $simboloMoneda . number_format($ticketPromedio ?? 0, 2)"
            icon="trending-up"
            :href="route('reportes.ventas')"
        />

        <x-kpi-card
            label="Productos Activos"
            :value="number_format($totalProductos ?? 0)"
            :context="($totalCategorias ?? 0) . ' categorías comerciales'"
            icon="check"
            :href="route('productos.index')"
        />

        <x-kpi-card
            label="Stock Bajo"
            :value="number_format($totalBajoStock ?? 0)"
            :context="($totalBajoStock ?? 0) > 0 ? 'Artículos requieren reposición' : 'Inventario en niveles óptimos'"
            :alert="($totalBajoStock ?? 0) > 0 ? 'warning' : false"
            icon="alert"
            :href="route('productos.index', ['bajo_stock' => 1])"
        />
    </div>

    <!-- Sección Principal: Tendencia de Ventas (8 cols) y Estado de Caja + Top Productos (4 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Columna Izquierda (8 cols): Gráfico Semanal + Últimas Transacciones -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Tarjeta de Tendencia de Ventas (Últimos 7 días) -->
            <x-card title="Tendencia de Ventas (Últimos 7 días)" subtitle="Comportamiento diario de ingresos completados en la sucursal actual">
                <x-slot:actions>
                    <div class="text-right">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-text-muted block">Total 7 días</span>
                        <span class="text-base font-semibold text-text-primary tabular-nums">{{ $simboloMoneda }}{{ number_format($totalSemana, 2) }}</span>
                    </div>
                </x-slot:actions>

                @if($totalSemana > 0)
                    <div class="grid grid-cols-7 gap-2 sm:gap-4 items-end h-44 pt-6">
                        @foreach($ventas7Dias as $item)
                            @php
                                $pct = $maxVentaDia > 0 ? max(8, round(($item['total'] / $maxVentaDia) * 100)) : 8;
                            @endphp
                            <div class="flex flex-col items-center h-full justify-end group relative"
                                 title="{{ $item['dia'] }} ({{ $item['fecha'] }}): {{ $simboloMoneda }}{{ number_format($item['total'], 2) }}">
                                <div class="absolute -top-7 opacity-0 group-hover:opacity-100 transition-opacity bg-brand-900 text-white text-[10px] font-semibold py-0.5 px-2 rounded pointer-events-none whitespace-nowrap z-10 tabular-nums shadow-xs">
                                    {{ $simboloMoneda }}{{ number_format($item['total'], 0, ',', '.') }}
                                </div>
                                <div class="w-full max-w-[44px] rounded-t-md transition-all duration-200 {{ $item['total'] > 0 ? 'bg-brand-600 group-hover:bg-brand-500' : 'bg-surface-muted border border-border' }}"
                                     style="height: {{ $pct }}%;">
                                </div>
                                <div class="text-[11px] font-medium text-text-secondary mt-2 truncate w-full text-center tabular-nums">
                                    {{ $item['dia'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state
                        title="Sin movimientos en los últimos 7 días"
                        description="Las ventas completadas en el punto de venta se graficarán automáticamente por día."
                    >
                        @can('ventas.crear')
                            <x-button variant="primary" size="sm" href="{{ route('pos.index') }}">
                                Ir al Terminal POS
                            </x-button>
                        @endcan
                    </x-empty-state>
                @endif
            </x-card>

            <!-- Tabla de Últimas Transacciones -->
            <x-card padding="p-0" title="Últimas Transacciones" subtitle="Ventas recientes registradas en la sucursal activa">
                <x-slot:actions>
                    @can('ventas.ver')
                        <x-button variant="outline" size="sm" href="{{ route('ventas.index') }}">
                            Ver historial
                        </x-button>
                    @endcan
                </x-slot:actions>

                @if($listaUltimasVentas->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="table tabular-nums">
                            <thead>
                                <tr>
                                    <th>Comprobante</th>
                                    <th>Cliente</th>
                                    <th>Fecha / Hora</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($listaUltimasVentas as $venta)
                                    <tr>
                                        <td>
                                            <div class="font-mono text-xs font-semibold text-text-primary">{{ $venta->numero_venta }}</div>
                                            <div class="text-[11px] text-text-muted">{{ $venta->tipo_comprobante?->value ?? 'TICKET' }}</div>
                                        </td>
                                        <td>
                                            <div class="text-xs font-medium text-text-primary truncate max-w-[180px]">
                                                {{ $venta->cliente?->razon_social ?? 'Consumidor Final' }}
                                            </div>
                                            <div class="text-[11px] text-text-muted">
                                                Cajero: {{ $venta->usuario?->name ?? 'Sistema' }}
                                            </div>
                                        </td>
                                        <td class="text-xs text-text-secondary tabular-nums whitespace-nowrap">
                                            {{ $venta->fecha?->format('d/m/Y H:i') ?? $venta->created_at?->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="text-center">
                                            <x-status-badge :status="$venta->estado" size="sm" />
                                        </td>
                                        <td class="text-right font-semibold text-text-primary tabular-nums whitespace-nowrap">
                                            {{ $simboloMoneda }}{{ number_format($venta->total, 2) }}
                                        </td>
                                        <td class="text-right">
                                            @can('ventas.ver')
                                                <x-icon-button
                                                    variant="ghost"
                                                    size="sm"
                                                    icon="eye"
                                                    title="Ver comprobante {{ $venta->numero_venta }}"
                                                    aria-label="Ver comprobante {{ $venta->numero_venta }}"
                                                    href="{{ route('ventas.show', $venta) }}"
                                                />
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4">
                        <x-empty-state
                            title="Sin transacciones recientes"
                            description="Aún no se han emitido comprobantes de venta en este período."
                        />
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Columna Derecha (4 cols): Estado de Caja + Control de Stock / Artículos -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Panel de Estado de Caja -->
            <x-card title="Estado de Caja" subtitle="Control de turno operativo en sucursal">
                <x-slot:actions>
                    @if($sesionCajaActiva)
                        <x-status-badge status="ABIERTA" size="sm" />
                    @else
                        <x-status-badge status="CERRADA" size="sm" />
                    @endif
                </x-slot:actions>

                @if($sesionCajaActiva)
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between py-1.5 border-b border-border">
                            <span class="text-text-secondary">Caja activa</span>
                            <span class="font-semibold text-text-primary">{{ $sesionCajaActiva->caja?->nombre ?? 'Caja Principal' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-border">
                            <span class="text-text-secondary">Cajero responsable</span>
                            <span class="font-medium text-text-primary truncate max-w-[160px]">{{ $sesionCajaActiva->cajero?->name ?? auth()->user()->name }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-border">
                            <span class="text-text-secondary">Fondo de apertura</span>
                            <span class="font-semibold text-text-primary tabular-nums">{{ $simboloMoneda }}{{ number_format($sesionCajaActiva->monto_apertura ?? 0, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-text-secondary">Apertura</span>
                            <span class="font-mono text-text-primary tabular-nums">{{ $sesionCajaActiva->fecha_apertura?->format('d/m H:i') }}</span>
                        </div>

                        <div class="pt-2 flex items-center gap-2">
                            @can('ventas.crear')
                                <x-button variant="primary" size="sm" class="flex-1" href="{{ route('pos.index') }}">
                                    Abrir Terminal POS
                                </x-button>
                            @endcan
                            @can('caja.ver')
                                <x-button variant="outline" size="sm" href="{{ route('cajas.index') }}">
                                    Ver Cajas
                                </x-button>
                            @endcan
                        </div>
                    </div>
                @else
                    <div class="space-y-3">
                        <p class="text-xs text-text-secondary leading-relaxed">
                            No hay un turno de caja abierto en este momento. Abre un turno con fondo inicial para habilitar la facturación en mostrador.
                        </p>
                        @can('caja.ver')
                            <x-button variant="secondary" size="sm" class="w-full" href="{{ route('cajas.index') }}">
                                Gestionar Apertura de Caja
                            </x-button>
                        @endcan
                    </div>
                @endif
            </x-card>

            <!-- Panel de Artículos / Monitoreo de Stock -->
            <x-card
                :title="$prodCriticos->isNotEmpty() ? 'Artículos con Stock Crítico' : 'Artículos Recientes en Catálogo'"
                subtitle="Nivel de existencias y precio unitario"
            >
                <x-slot:actions>
                    <a href="{{ route('productos.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">
                        Ver todos
                    </a>
                </x-slot:actions>

                @if($articulosAMostrar->isNotEmpty())
                    <div class="space-y-3.5">
                        @foreach($articulosAMostrar as $art)
                            @php
                                $barraPct = min(100, max(8, round(((float) $art->stock / $maxStockRef) * 100)));
                            @endphp
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <a href="{{ route('productos.edit', $art) }}" class="font-medium text-text-primary hover:text-brand-600 truncate">
                                        {{ $art->nombre }}
                                    </a>
                                    <span class="font-mono font-semibold text-text-primary tabular-nums shrink-0">
                                        {{ number_format($art->stock, 0) }} {{ $art->unidadMedida?->codigo ?? 'UND' }}
                                    </span>
                                </div>
                                <div class="w-full h-1.5 bg-surface-muted rounded-full overflow-hidden border border-border">
                                    <div class="h-full rounded-full {{ $art->stock <= 0 ? 'bg-danger' : ($art->tieneBajoStock() ? 'bg-warning' : 'bg-brand-600') }}"
                                         style="width: {{ $barraPct }}%;"></div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-text-muted tabular-nums">
                                    <span>SKU: {{ $art->codigo ?? 'N/A' }}</span>
                                    <span>PVP: {{ $simboloMoneda }}{{ number_format($art->precio_venta, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-text-secondary text-center py-4">
                        No hay productos registrados en el catálogo.
                    </p>
                @endif
            </x-card>

            <!-- Resumen Financiero Complementario -->
            <x-card title="Indicadores de Cartera y Margen" subtitle="Consolidado del período en curso">
                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between py-1.5 border-b border-border">
                        <span class="text-text-secondary">Utilidad bruta estimada (Mes)</span>
                        <span class="font-semibold text-text-primary tabular-nums">{{ $simboloMoneda }}{{ number_format($utilidadMes ?? 0, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-border">
                        <span class="text-text-secondary">Cartera pendiente por cobrar</span>
                        <span class="font-semibold {{ ($carteraPendiente ?? 0) > 0 ? 'text-warning-text' : 'text-text-primary' }} tabular-nums">
                            {{ $simboloMoneda }}{{ number_format($carteraPendiente ?? 0, 2) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-text-secondary">NIT / Moneda operativa</span>
                        <span class="font-mono font-medium text-text-primary tabular-nums">
                            {{ $empresaActualDash?->nit ?? 'Global' }} · {{ $empresaActualDash?->moneda ?? 'COP' }}
                        </span>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
