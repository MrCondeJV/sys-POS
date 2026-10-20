@extends('layouts.app')

@section('title', 'Gestión de Suscripción y Planes')

@section('content')
<div class="space-y-6">
    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-text-primary">Suscripción y Facturación</h1>
            <p class="text-sm text-text-muted mt-1">Gestiona tu plan comercial, vigencia y métodos de pago de Wompi Colombia.</p>
        </div>
        <div class="flex items-center gap-2">
            @if($suscripcion)
                <x-status-badge :status="$suscripcion->estado" />
            @else
                <x-badge variant="neutral">Sin Suscripción Activa</x-badge>
            @endif
        </div>
    </div>

    {{-- Banner de Período de Gracia o Vencimiento --}}
    @if(isset($suscripcionEnGracia) && $suscripcionEnGracia)
        <div class="p-4 rounded-lg bg-warning-bg border border-warning-border text-warning-text flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="text-sm">
                <p class="font-semibold">Período de Gracia Activo</p>
                <p class="mt-0.5">Tu última factura de renovación no pudo ser cobrada. Dispones de un período de gracia hasta el <strong>{{ $suscripcionFechaGraciaFin ? $suscripcionFechaGraciaFin->format('d/m/Y H:i') : 'próximos días' }}</strong> antes de que tu cuenta pase a modo solo lectura.</p>
            </div>
        </div>
    @elseif($suscripcion && ! $suscripcion->isVigente())
        <div class="p-4 rounded-lg bg-danger-bg border border-danger-border text-danger-text flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            <div class="text-sm">
                <p class="font-semibold">Suscripción Vencida — Modo Solo Lectura</p>
                <p class="mt-0.5">Puedes consultar y exportar tus reportes e historial comercial. Para registrar nuevas ventas, abrir caja o gestionar inventario, reactiva tu plan a continuación.</p>
            </div>
        </div>
    @endif

    {{-- Estado Actual de la Suscripción --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-kpi-card
            title="Plan Actual"
            :value="$planActual ? $planActual->nombre : 'Sin Plan Asignado'"
            color="brand"
        />
        <x-kpi-card
            title="Ciclo de Cobro"
            :value="$suscripcion ? $suscripcion->ciclo_facturacion : 'N/A'"
            color="neutral"
        />
        <x-kpi-card
            title="Vencimiento"
            :value="$suscripcion && $suscripcion->fecha_fin ? $suscripcion->fecha_fin->format('d/m/Y') : 'Indefinido'"
            :color="$suscripcion && $suscripcion->isVigente() ? 'success' : 'danger'"
        />
        <x-kpi-card
            title="Usuarios del Tenant"
            :value="$empresa->users()->count() . ($planActual ? ' / ' . ($planActual->esIlimitadoUsuarios() ? '∞' : $planActual->limite_usuarios) : '')"
            color="neutral"
        />
    </div>

    {{-- Catálogo de Planes Comerciales --}}
    <div>
        <h2 class="text-lg font-semibold text-text-primary mb-4">Planes Disponibles</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($planes as $p)
                @php
                    $esActual = $planActual && $planActual->id === $p->id;
                @endphp
                <div class="bg-surface rounded-xl border {{ $esActual ? 'border-brand-500 shadow-sm ring-1 ring-brand-500' : 'border-border' }} p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-text-primary">{{ $p->nombre }}</h3>
                            @if($esActual)
                                <x-badge variant="brand">Plan Actual</x-badge>
                            @endif
                        </div>
                        <p class="text-sm text-text-muted min-h-[40px]">{{ $p->descripcion }}</p>

                        {{-- Precios --}}
                        <div class="my-6">
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-extrabold text-text-primary tabular-nums">${{ number_format($p->precio_mensual, 0, ',', '.') }}</span>
                                <span class="text-xs text-text-muted">COP / mes</span>
                            </div>
                            <p class="text-xs text-text-muted mt-1">O ${{ number_format($p->precio_anual, 0, ',', '.') }} COP facturado anual (ahorro de 2 meses)</p>
                        </div>

                        {{-- Características --}}
                        <ul class="space-y-2.5 text-sm text-text-primary border-t border-border pt-4">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-success-text shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ $p->esIlimitadoSucursales() ? 'Sucursales Ilimitadas' : 'Hasta ' . $p->limite_sucursales . ' ' . Str::plural('sucursal', $p->limite_sucursales) }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-success-text shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ $p->esIlimitadoUsuarios() ? 'Usuarios Ilimitados' : 'Hasta ' . $p->limite_usuarios . ' ' . Str::plural('usuario', $p->limite_usuarios) }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 {{ $p->permite_facturacion_electronica ? 'text-success-text' : 'text-text-muted' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $p->permite_facturacion_electronica ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"/>
                                </svg>
                                <span class="{{ ! $p->permite_facturacion_electronica ? 'text-text-muted' : '' }}">Facturación Electrónica DIAN</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 {{ $p->permite_api ? 'text-success-text' : 'text-text-muted' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $p->permite_api ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"/>
                                </svg>
                                <span class="{{ ! $p->permite_api ? 'text-text-muted' : '' }}">Integración API REST</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8 space-y-2">
                        <a href="{{ route('suscripcion.checkout', ['plan' => $p, 'ciclo' => 'MENSUAL']) }}" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-semibold rounded-lg bg-brand-600 text-white hover:bg-brand-700 transition">
                            Pagar Mensual (${{ number_format($p->precio_mensual, 0, ',', '.') }})
                        </a>
                        <a href="{{ route('suscripcion.checkout', ['plan' => $p, 'ciclo' => 'ANUAL']) }}" class="w-full inline-flex justify-center items-center px-4 py-2 text-xs font-medium rounded-lg border border-border text-text-primary hover:bg-surface-muted transition">
                            Pagar Anual (${{ number_format($p->precio_anual, 0, ',', '.') }})
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3">
                    <x-empty-state
                        title="No hay planes configurados"
                        description="Comunícate con el Super Administrador para activar los planes comerciales."
                    />
                </div>
            @endforelse
        </div>
    </div>

    {{-- Historial Reciente de Transacciones Wompi --}}
    <div class="bg-surface rounded-xl border border-border p-6">
        <h2 class="text-base font-semibold text-text-primary mb-4">Historial de Pagos y Transacciones Wompi</h2>
        @if($pagosRecientes->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border text-text-muted text-xs uppercase font-semibold">
                            <th class="pb-3">Referencia</th>
                            <th class="pb-3">Plan / Ciclo</th>
                            <th class="pb-3">Monto</th>
                            <th class="pb-3">Método</th>
                            <th class="pb-3">Estado</th>
                            <th class="pb-3">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border text-text-primary">
                        @foreach($pagosRecientes as $pago)
                            <tr>
                                <td class="py-3 font-mono text-xs">{{ $pago->reference }}</td>
                                <td class="py-3">{{ $pago->plan?->nombre ?? 'N/A' }} <span class="text-xs text-text-muted">({{ $pago->ciclo_facturacion }})</span></td>
                                <td class="py-3 font-semibold tabular-nums">${{ number_format($pago->amount_in_cents / 100, 0, ',', '.') }} COP</td>
                                <td class="py-3 text-xs text-text-muted">{{ $pago->payment_method_type ?? 'Pendiente' }}</td>
                                <td class="py-3">
                                    @if($pago->status === 'APPROVED')
                                        <x-badge variant="success">Aprobado</x-badge>
                                    @elseif($pago->status === 'PENDING')
                                        <x-badge variant="warning">Pendiente</x-badge>
                                    @else
                                        <x-badge variant="danger">{{ $pago->status }}</x-badge>
                                    @endif
                                </td>
                                <td class="py-3 text-xs text-text-muted">{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-text-muted">No se registran transacciones previas en esta empresa.</p>
        @endif
    </div>
</div>
@endsection
