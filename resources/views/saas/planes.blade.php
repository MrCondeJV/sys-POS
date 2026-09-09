@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <div class="text-center space-y-2">
        <h1 class="text-3xl font-extrabold text-gray-900">Planes y Precios Comerciales</h1>
        <p class="text-base text-gray-500">Selecciona el plan que mejor se adapte al crecimiento y necesidades de tu negocio.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($planes as $p)
            @php $isActual = $suscripcionActual && $suscripcionActual->plan_id === $p->id; @endphp
            <div class="bg-white rounded-xl shadow-lg border {{ $isActual ? 'border-indigo-500 ring-2 ring-indigo-500' : 'border-gray-200' }} p-6 flex flex-col justify-between">
                <div>
                    @if($isActual)
                        <span class="inline-flex px-3 py-1 bg-indigo-100 text-indigo-800 text-xs font-bold rounded-full mb-3">PLAN ACTUAL</span>
                    @endif
                    <h3 class="text-xl font-bold text-gray-900">{{ $p->nombre }}</h3>
                    <p class="text-xs text-gray-500 mt-1 min-h-[32px]">{{ $p->descripcion }}</p>

                    <div class="my-6">
                        <span class="text-3xl font-extrabold text-gray-900">${{ number_format($p->precio_mensual, 0) }}</span>
                        <span class="text-xs text-gray-500">/ mes COP</span>
                        <p class="text-xs text-indigo-600 mt-1">${{ number_format($p->precio_anual, 0) }} / año (Ahorro 2 meses)</p>
                    </div>

                    <ul class="space-y-3 text-sm text-gray-600 border-t pt-4">
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">&check;</span>
                            <span>{{ $p->esIlimitadoSucursales() ? 'Sucursales ilimitadas' : $p->limite_sucursales . ' sucursales' }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">&check;</span>
                            <span>{{ $p->esIlimitadoUsuarios() ? 'Usuarios ilimitados' : $p->limite_usuarios . ' usuarios' }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="{{ $p->permite_facturacion_electronica ? 'text-emerald-500 font-bold' : 'text-gray-300' }}">
                                {{ $p->permite_facturacion_electronica ? '✓' : '✗' }}
                            </span>
                            <span class="{{ $p->permite_facturacion_electronica ? 'text-gray-800' : 'text-gray-400' }}">Facturación electrónica DIAN</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="{{ $p->permite_api ? 'text-emerald-500 font-bold' : 'text-gray-300' }}">
                                {{ $p->permite_api ? '✓' : '✗' }}
                            </span>
                            <span class="{{ $p->permite_api ? 'text-gray-800' : 'text-gray-400' }}">Acceso a API RESTful</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-8 pt-4 border-t">
                    @if($isActual)
                        <button disabled class="w-full py-2 bg-gray-100 text-gray-500 rounded-md font-semibold text-sm cursor-default">
                            Plan Activo
                        </button>
                    @else
                        <form method="POST" action="{{ route('saas.cambiar-plan') }}">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $p->id }}">
                            <input type="hidden" name="ciclo" value="MENSUAL">
                            <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold text-sm shadow transition">
                                Contratar este Plan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
