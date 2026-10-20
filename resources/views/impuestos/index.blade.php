@extends('layouts.app')

@section('title', 'Configuración de Impuestos')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Impuestos y Gravámenes"
        description="Configure tarifas de IVA, productos exentos y excluidos de acuerdo con la normatividad tributaria."
    >
        <x-slot:actions>
            <a href="{{ route('impuestos.create') }}" class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Impuesto
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Tabla de Impuestos --}}
    <div class="card overflow-hidden">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre / Tarifa</th>
                        <th>Tipo</th>
                        <th class="text-right">Porcentaje (%)</th>
                        <th class="text-center">Predeterminado</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($impuestos as $imp)
                    <tr>
                        <td class="font-mono font-semibold text-slate-900 text-xs">
                            {{ $imp->codigo }}
                        </td>
                        <td>
                            <div class="font-semibold text-slate-900">{{ $imp->nombre }}</div>
                            @if($imp->descripcion)
                                <div class="text-xs text-slate-500">{{ $imp->descripcion }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $imp->tipo->badgeClasses() }}">
                                {{ $imp->tipo->value }}
                            </span>
                        </td>
                        <td class="text-right tabular-nums font-mono font-semibold text-slate-900">
                            {{ number_format($imp->porcentaje, 1) }}%
                        </td>
                        <td class="text-center">
                            @if($imp->por_defecto)
                                <span class="badge badge-success">
                                    ★ Por Defecto
                                </span>
                            @else
                                <form action="{{ route('impuestos.por-defecto', $imp) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-slate-500 hover:text-brand-600 font-medium transition">
                                        Fijar predeterminado
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $imp->estado->value === 'ACTIVO' ? 'badge-success' : 'badge-neutral' }}">
                                {{ $imp->estado->value }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('impuestos.edit', $imp) }}" class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-slate-100 rounded-lg transition" title="Editar">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>
                                <x-modal-confirm
                                    title="¿Eliminar impuesto?"
                                    message="Se eliminará el impuesto «{{ $imp->nombre }} ({{ $imp->codigo }})»."
                                    action="{{ route('impuestos.destroy', $imp) }}"
                                    method="DELETE"
                                    confirmText="Eliminar Impuesto"
                                    variant="danger"
                                >
                                    <x-slot:trigger>
                                        <button type="button" class="p-1.5 text-slate-400 hover:text-danger-text hover:bg-danger-bg rounded-lg transition" title="Eliminar">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </x-slot:trigger>
                                </x-modal-confirm>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state
                                title="Sin impuestos configurados"
                                description="No hay tarifas de impuestos configuradas para esta empresa."
                            />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($impuestos->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50/50">
            {{ $impuestos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
