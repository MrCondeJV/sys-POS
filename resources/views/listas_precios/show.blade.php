@extends('layouts.app')

@section('title', 'Precios de Lista: ' . $listaPrecio->nombre)

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6" x-data="{
    deleteModalOpen: false,
    deleteFormAction: '',
    confirmDelete(action) {
        this.deleteFormAction = action;
        this.deleteModalOpen = true;
    }
}">

    <!-- Header Section -->
    <x-page-header
        :title="$listaPrecio->nombre"
        subtitle="Regla Base: {{ $listaPrecio->tipo_ajuste->label() }} &bull; Ajuste General: {{ $listaPrecio->porcentaje_defecto }}% &bull; Clientes asignados: {{ $listaPrecio->clientes_count }}"
        :breadcrumbs="[
            ['label' => 'Listas de Precios', 'url' => route('listas-precios.index')],
            ['label' => 'Precios Específicos']
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center space-x-2">
                <x-status-badge :status="$listaPrecio->estado" size="sm" />
                @if($listaPrecio->es_predeterminada)
                    <x-badge status="success" size="sm">Predeterminada</x-badge>
                @endif
                <x-button variant="outline" size="sm" href="{{ route('listas-precios.edit', $listaPrecio) }}">
                    Editar Reglas
                </x-button>
                <x-button variant="secondary" size="sm" href="{{ route('listas-precios.index') }}">
                    Volver
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Formulario para Asignar / Fijar Precio Específico -->
    @can('update', $listaPrecio)
    <div class="bg-white border border-slate-200/80 rounded-xl p-5 shadow-xs">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">
            Fijar Precio Individual a un Producto
        </h3>
        <form action="{{ route('listas-precios.precios.store', $listaPrecio) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            @csrf
            <div class="sm:col-span-7">
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Producto del Catálogo</label>
                <select name="producto_id" required class="input">
                    <option value="">Buscar producto...</option>
                    @foreach($productos as $prod)
                        <option value="{{ $prod->id }}">
                            {{ $prod->nombre }} (Precio Base: ${{ number_format($prod->precio_venta, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Precio Especial ($)</label>
                <input type="number" step="0.01" min="0" name="precio" required placeholder="0.00"
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-bold text-slate-900 tabular-nums focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>
            <div class="sm:col-span-2">
                <x-button variant="primary" size="sm" type="submit" class="w-full">
                    Asignar Precio
                </x-button>
            </div>
        </form>
    </div>
    @endcan

    <!-- Listado de Precios Personalizados Configurados -->
    <x-table>
        <x-slot:thead>
            <tr>
                <th class="px-5 py-3 text-left uppercase">Producto</th>
                <th class="px-4 py-3 text-left uppercase">Categoría</th>
                <th class="px-4 py-3 text-right uppercase">Precio Base</th>
                <th class="px-4 py-3 text-right uppercase">Precio en esta Lista</th>
                <th class="px-4 py-3 text-center uppercase">Variación</th>
                <th class="px-5 py-3 text-right uppercase">Acción</th>
            </tr>
        </x-slot:thead>

        @forelse($detalles as $det)
        @php
            $base = (float) $det->producto->precio_venta;
            $especial = (float) $det->precio;
            $diff = $especial - $base;
            $pct = $base > 0 ? round(($diff / $base) * 100, 1) : 0;
        @endphp
        <tr class="hover:bg-slate-50/70 transition">
            <td class="px-5 py-3 text-xs">
                <div class="font-semibold text-slate-900">{{ $det->producto->nombre }}</div>
                <div class="text-[11px] font-mono text-slate-400">{{ $det->producto->codigo ?? $det->producto->sku }}</div>
            </td>
            <td class="px-4 py-3 text-xs text-slate-600">
                {{ $det->producto->categoria?->nombre ?? 'General' }}
            </td>
            <td class="px-4 py-3 text-right text-xs text-slate-500 tabular-nums">
                ${{ number_format($base, 2) }}
            </td>
            <td class="px-4 py-3 text-right font-bold text-slate-900 text-xs tabular-nums">
                ${{ number_format($especial, 2) }}
            </td>
            <td class="px-4 py-3 text-center">
                @if($diff < 0)
                    <x-badge status="success" size="sm">{{ $pct }}%</x-badge>
                @elseif($diff > 0)
                    <x-badge status="warning" size="sm">+{{ $pct }}%</x-badge>
                @else
                    <span class="text-slate-400 font-mono text-xs">0%</span>
                @endif
            </td>
            <td class="px-5 py-3 text-right whitespace-nowrap">
                @can('update', $listaPrecio)
                <x-button variant="danger" size="sm" type="button" @click="confirmDelete('{{ route('listas-precios.precios.destroy', [$listaPrecio, $det]) }}')">
                    Quitar
                </x-button>
                @endcan
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="px-5 py-12 text-center">
                <x-empty-state
                    title="No hay precios individuales asignados"
                    description="Se aplicará la regla general predeterminada a todos los productos."
                />
            </td>
        </tr>
        @endforelse

        @if($detalles->hasPages())
        <x-slot:pagination>
            {{ $detalles->links() }}
        </x-slot:pagination>
        @endif
    </x-table>

    <!-- Modal Confirmación Eliminación de Precio -->
    <form id="deletePriceForm" :action="deleteFormAction" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <x-modal-confirm
        show="deleteModalOpen"
        title="Quitar Precio Especial"
        message="¿Estás seguro de quitar el precio específico de este producto en la lista?"
        confirm-text="Sí, quitar"
        cancel-text="Cancelar"
        confirm-variant="danger"
        @confirm="document.getElementById('deletePriceForm').submit()"
        @cancel="deleteModalOpen = false"
    />
</div>
@endsection
