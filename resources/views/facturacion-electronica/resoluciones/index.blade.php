@extends('layouts.app')

@section('title', 'Resoluciones DIAN')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Resoluciones de Facturación DIAN"
        subtitle="Rangos de numeración, prefijos y vigencias autorizadas">
        <x-slot:actions>
            <a href="{{ route('facturacion-electronica.index') }}" class="btn-secondary">
                Ver Facturas Electrónicas
            </a>
            @can('create', App\Models\ResolucionFacturacion::class)
            <a href="{{ route('resoluciones.create') }}" class="btn-primary">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Resolución
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card p-0 overflow-hidden">
        <div class="@container hidden lg:block overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Resolución</th>
                        <th>Prefijo</th>
                        <th class="hidden @4xl:table-cell">Rango Autorizado</th>
                        <th class="text-right">Actual</th>
                        <th class="hidden @5xl:table-cell">Vigencia</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resoluciones as $res)
                        <tr>
                            <td class="font-semibold text-slate-800 text-xs">
                                {{ $res->numero_resolucion }}
                                @if($res->es_predeterminada)
                                    <span class="badge badge-brand ml-1">Predeterminada</span>
                                @endif
                                <div class="@5xl:hidden font-normal text-[11px] text-slate-500 font-mono">Hasta {{ $res->fecha_vigencia->format('d/m/Y') }}</div>
                            </td>
                            <td class="font-mono font-bold text-xs text-brand-700">{{ $res->prefijo }}</td>
                            <td class="hidden @4xl:table-cell font-mono text-xs tabular-nums whitespace-nowrap">{{ number_format($res->rango_desde) }} - {{ number_format($res->rango_hasta) }}</td>
                            <td class="text-right font-mono font-semibold text-xs text-slate-900 tabular-nums">{{ number_format($res->consecutivo_actual) }}</td>
                            <td class="hidden @5xl:table-cell text-xs text-slate-600 font-mono">
                                <div>Desde: {{ $res->fecha_inicio->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-400">Hasta: {{ $res->fecha_vigencia->format('d/m/Y') }}</div>
                            </td>
                            <td class="text-center">
                                @if($res->estaVigente())
                                    <span class="badge badge-success">Activa y Vigente</span>
                                @else
                                    <span class="badge badge-danger">{{ $res->estado->label() }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    @can('update', $res)
                                    <a href="{{ route('resoluciones.edit', $res) }}" class="btn-secondary py-1 px-2.5 text-xs">
                                        Editar
                                    </a>
                                    @endcan
                                    @can('delete', $res)
                                    <x-modal-confirm
                                        title="¿Eliminar resolución DIAN?"
                                        message="Se eliminará la resolución {{ $res->numero_resolucion }} ({{ $res->prefijo }}). Solo puede eliminarse si no tiene documentos emitidos."
                                        action="{{ route('resoluciones.destroy', $res) }}"
                                        method="DELETE"
                                        confirmText="Eliminar"
                                        variant="danger"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="btn-danger py-1 px-2 text-xs">
                                                Eliminar
                                            </button>
                                        </x-slot:trigger>
                                    </x-modal-confirm>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12">
                                <div class="empty-state">
                                    <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="empty-state-title">No hay resoluciones configuradas</p>
                                    <p class="empty-state-desc">Registra los rangos autorizados por la DIAN para facturar electrónicamente.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Móvil y Tablet -->
        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($resoluciones as $res)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-800 text-sm">
                            {{ $res->numero_resolucion }}
                            <span class="font-mono font-bold text-xs text-brand-700 ml-1">{{ $res->prefijo }}</span>
                        </div>
                        @if($res->es_predeterminada)
                            <span class="badge badge-brand mt-1">Predeterminada</span>
                        @endif
                    </div>
                    @if($res->estaVigente())
                        <span class="badge badge-success">Activa y Vigente</span>
                    @else
                        <span class="badge badge-danger">{{ $res->estado->label() }}</span>
                    @endif
                </div>

                <div class="text-xs text-slate-600 space-y-1 font-mono tabular-nums">
                    <div><span class="font-sans text-slate-400">Rango:</span> {{ number_format($res->rango_desde) }} - {{ number_format($res->rango_hasta) }}</div>
                    <div><span class="font-sans text-slate-400">Actual:</span> <span class="font-semibold text-slate-900">{{ number_format($res->consecutivo_actual) }}</span></div>
                    <div><span class="font-sans text-slate-400">Vigencia:</span> {{ $res->fecha_inicio->format('d/m/Y') }} &rarr; {{ $res->fecha_vigencia->format('d/m/Y') }}</div>
                </div>

                @canany(['update', 'delete'], $res)
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    @can('update', $res)
                    <a href="{{ route('resoluciones.edit', $res) }}" class="btn-secondary py-1 px-2.5 text-xs">
                        Editar
                    </a>
                    @endcan
                    @can('delete', $res)
                    <x-modal-confirm
                        title="¿Eliminar resolución DIAN?"
                        message="Se eliminará la resolución {{ $res->numero_resolucion }} ({{ $res->prefijo }}). Solo puede eliminarse si no tiene documentos emitidos."
                        action="{{ route('resoluciones.destroy', $res) }}"
                        method="DELETE"
                        confirmText="Eliminar"
                        variant="danger"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-danger py-1 px-2 text-xs">
                                Eliminar
                            </button>
                        </x-slot:trigger>
                    </x-modal-confirm>
                    @endcan
                </div>
                @endcanany
            </div>
            @empty
            <div class="p-8 text-center text-xs text-slate-500">
                No hay resoluciones configuradas.
            </div>
            @endforelse
        </div>
        @if($resoluciones->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $resoluciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
