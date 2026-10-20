@extends('layouts.app')

@section('title', 'Facturación Electrónica DIAN')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Facturación Electrónica DIAN"
        subtitle="Comprobantes fiscales transmitidos, CUFE y validaciones oficiales">
        <x-slot:actions>
            <a href="{{ route('resoluciones.index') }}" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Resoluciones DIAN
            </a>
            <a href="{{ route('documentos.index') }}" class="btn-primary">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Emitir desde Ventas
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card p-4">
        <form method="GET" action="{{ route('facturacion-electronica.index') }}" class="flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-[220px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por consecutivo o CUFE..."
                    class="input pl-9 text-xs">
            </div>

            <select name="estado" class="input text-xs w-auto">
                <option value="">Todos los Estados DIAN</option>
                @foreach($estadosDian as $est)
                    <option value="{{ $est->value }}" @selected(request('estado') === $est->value)>{{ $est->label() }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-primary">
                Filtrar
            </button>
        </form>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Consecutivo</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th class="text-right">Total</th>
                        <th>CUFE / CUDE</th>
                        <th class="text-center">Estado DIAN</th>
                        <th class="text-right">Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documentos as $doc)
                        <tr>
                            <td class="font-semibold text-slate-900 font-mono text-xs">{{ $doc->consecutivo_completo }}</td>
                            <td>
                                <span class="badge badge-neutral">
                                    {{ $doc->tipo->label() }}
                                </span>
                            </td>
                            <td class="text-xs">
                                <div class="font-semibold text-slate-900">{{ $doc->documentoVenta->cliente?->razon_social ?? 'Consumidor Final' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $doc->documentoVenta->cliente?->numero_documento ?? '222222222222' }}</div>
                            </td>
                            <td class="text-right font-mono font-semibold text-xs text-slate-900 tabular-nums">
                                ${{ number_format($doc->documentoVenta->total, 2) }}
                            </td>
                            <td class="font-mono text-[11px] text-slate-500 max-w-xs truncate" title="{{ $doc->cufe }}">
                                {{ $doc->cufe ? substr($doc->cufe, 0, 16) . '...' : 'Pendiente' }}
                            </td>
                            <td class="text-center">
                                @if($doc->estado_dian === \App\Enums\EstadoDian::ACEPTADO)
                                    <span class="badge badge-success">✓ Aceptado DIAN</span>
                                @elseif($doc->estado_dian === \App\Enums\EstadoDian::RECHAZADO)
                                    <span class="badge badge-danger">✗ Rechazado</span>
                                @else
                                    <span class="badge badge-warning">{{ $doc->estado_dian->label() }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('facturacion-electronica.show', $doc) }}" class="btn-secondary py-1 px-2.5 text-xs">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12">
                                <div class="empty-state">
                                    <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="empty-state-title">No hay documentos electrónicos emitidos</p>
                                    <p class="empty-state-desc">Las facturas electrónicas transmitidas a la DIAN aparecerán registradas aquí.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($documentos->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $documentos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
