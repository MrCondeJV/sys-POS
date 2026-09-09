@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Mi Suscripción y Límites</h1>
            <p class="text-sm text-gray-500">Administración del plan contratado, uso de recursos y facturación del servicio SaaS.</p>
        </div>
        <a href="{{ route('saas.planes') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
            Cambiar o Mejorar Plan
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-md text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tarjeta de Plan Actual -->
        <div class="bg-white shadow rounded-lg p-6 lg:col-span-1 border-t-4 border-indigo-600 space-y-4">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Plan Contratado</h2>
            @if($plan)
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ $plan->nombre }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $plan->descripcion }}</p>
                </div>
                <div class="pt-2">
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border {{ $suscripcion->estado->badgeClasses() }}">
                        {{ $suscripcion->estado->label() }}
                    </span>
                </div>
                <div class="border-t pt-4 text-sm text-gray-600 space-y-2">
                    <p><span class="font-medium text-gray-800">Ciclo:</span> {{ $suscripcion->ciclo_facturacion }}</p>
                    <p><span class="font-medium text-gray-800">Costo:</span> ${{ number_format($suscripcion->precio_pago, 2) }}</p>
                    <p><span class="font-medium text-gray-800">Vigencia hasta:</span> {{ $suscripcion->fecha_fin?->format('d/m/Y') ?? 'Indefinida' }}</p>
                </div>
            @else
                <p class="text-sm text-gray-500">No hay un plan activo configurado actualmente.</p>
                <a href="{{ route('saas.planes') }}" class="text-sm text-indigo-600 font-semibold hover:underline">Elegir un Plan &rarr;</a>
            @endif
        </div>

        <!-- Métricas de Límites vs Consumo -->
        <div class="bg-white shadow rounded-lg p-6 lg:col-span-2 space-y-6">
            <h2 class="text-base font-semibold text-gray-900">Uso de Recursos y Capacidad</h2>

            <!-- Sucursales -->
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-gray-700">Sucursales Activas</span>
                    <span class="text-gray-500">
                        {{ $sucursalesCount }} de {{ $plan && !$plan->esIlimitadoSucursales() ? $plan->limite_sucursales : 'Ilimitadas' }}
                    </span>
                </div>
                @php
                    $pctSucursales = ($plan && !$plan->esIlimitadoSucursales() && $plan->limite_sucursales > 0)
                        ? min(round(($sucursalesCount / $plan->limite_sucursales) * 100), 100)
                        : 20;
                @endphp
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $pctSucursales }}%"></div>
                </div>
            </div>

            <!-- Usuarios -->
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-gray-700">Usuarios del Sistema</span>
                    <span class="text-gray-500">
                        {{ $usuariosCount }} de {{ $plan && !$plan->esIlimitadoUsuarios() ? $plan->limite_usuarios : 'Ilimitados' }}
                    </span>
                </div>
                @php
                    $pctUsuarios = ($plan && !$plan->esIlimitadoUsuarios() && $plan->limite_usuarios > 0)
                        ? min(round(($usuariosCount / $plan->limite_usuarios) * 100), 100)
                        : 15;
                @endphp
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-emerald-600 h-2.5 rounded-full" style="width: {{ $pctUsuarios }}%"></div>
                </div>
            </div>

            <!-- Módulos Avanzados Disponibles -->
            <div class="border-t pt-4">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Módulos y Características</h3>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="flex items-center gap-2">
                        @if($plan?->permite_facturacion_electronica)
                            <span class="text-emerald-600 font-bold">&check;</span>
                        @else
                            <span class="text-gray-300">&cross;</span>
                        @endif
                        <span class="{{ $plan?->permite_facturacion_electronica ? 'text-gray-900 font-medium' : 'text-gray-400' }}">
                            Facturación Electrónica DIAN
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($plan?->permite_api)
                            <span class="text-emerald-600 font-bold">&check;</span>
                        @else
                            <span class="text-gray-300">&cross;</span>
                        @endif
                        <span class="{{ $plan?->permite_api ? 'text-gray-900 font-medium' : 'text-gray-400' }}">
                            API RESTful para Integraciones
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
